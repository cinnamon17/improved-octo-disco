<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Dto\TelegramBotUpdate;
use App\Dto\UpdateDto;
use App\Service\TelegramRouter;
use App\Service\TelegramSecretTokenValidator;
use App\Service\UpdateSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TelegramControllerTest extends TestCase
{
    private const VALID_TOKEN = 'secreto-de-prueba';

    private function createRequestMock(string $content, string $token = self::VALID_TOKEN): Request
    {
        // Request real: headers() es final y no se puede mockear.
        return new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN' => $token],
            $content
        );
    }

    private function createValidator(bool $valid = true): TelegramSecretTokenValidator
    {
        $validator = $this->createMock(TelegramSecretTokenValidator::class);
        $validator->method('isValid')->willReturn($valid);

        return $validator;
    }

    private function createController(
        TelegramRouter $router,
        UpdateSerializer $serializer,
        RequestStack $requestStack,
        ?TelegramSecretTokenValidator $validator = null
    ): TelegramController {
        return new TelegramController(
            $router,
            $serializer,
            $requestStack,
            $validator ?? $this->createValidator()
        );
    }

    public function testIndexHandlesValidRequestAndDelegatesToRouter(): void
    {
        $requestContent = '{"update_id": 12345, "message": {"text": "/start"}}';
        $updateDtoMock = $this->createStub(UpdateDto::class);
        $expectedResponse = new JsonResponse(['status' => 'handled by router'], 200);

        $requestMock = $this->createRequestMock($requestContent);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $serializerMock = $this->createMock(UpdateSerializer::class);
        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with($requestContent)
            ->willReturn($updateDtoMock);

        $routerMock = $this->createMock(TelegramRouter::class);
        $routerMock->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(TelegramBotUpdate::class))
            ->willReturn($expectedResponse);

        $controller = $this->createController($routerMock, $serializerMock, $requestStackMock);
        $actualResponse = $controller->index();

        $this->assertSame($expectedResponse, $actualResponse);
    }

    /**
     * Sin secret token valido, un successful_payment falsificado por cualquiera
     * acreditaria creditos. El 403 tiene que ocurrir antes de deserializar.
     */
    public function testIndexRejectsInvalidSecretTokenBeforeDeserializing(): void
    {
        $serializerMock = $this->createMock(UpdateSerializer::class);
        $serializerMock->expects($this->never())->method('deserialize');

        $routerMock = $this->createMock(TelegramRouter::class);
        $routerMock->expects($this->never())->method('handle');

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->createRequestMock('{}', 'token-invalido'));

        $controller = $this->createController(
            $routerMock,
            $serializerMock,
            $requestStackMock,
            $this->createValidator(false)
        );

        $response = $controller->index();

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testIndexReturnsIgnoredForEmptyContent(): void
    {
        $requestContent = '';

        $requestMock = $this->createRequestMock($requestContent);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $serializerMock = $this->createMock(UpdateSerializer::class);
        $serializerMock->expects($this->never())
            ->method('deserialize');

        $routerMock = $this->createMock(TelegramRouter::class);
        $routerMock->expects($this->never())
            ->method('handle');

        $controller = $this->createController($routerMock, $serializerMock, $requestStackMock);
        $actualResponse = $controller->index();

        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'ignored']),
            $actualResponse->getContent()
        );
    }

    public function testIndexReturnsIgnoredIfNoRequestIsAvailable(): void
    {
        $content = '';
        $request = $this->createRequestMock($content);
        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $serializerMock = $this->createMock(UpdateSerializer::class);
        $serializerMock->expects($this->never())->method('deserialize');
        $routerMock = $this->createMock(TelegramRouter::class);
        $routerMock->expects($this->never())->method('handle');

        $controller = $this->createController($routerMock, $serializerMock, $requestStackMock);
        $actualResponse = $controller->index();

        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'ignored']),
            $actualResponse->getContent()
        );
    }
}