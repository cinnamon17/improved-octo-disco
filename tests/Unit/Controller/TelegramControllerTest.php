<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Dto\TelegramBotUpdate;
use App\Dto\UpdateDto;
use App\Service\TelegramRouter;
use App\Service\UpdateSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\RequestStack;

class TelegramControllerTest extends TestCase
{
    private function createRequestMock(string $content): Request
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);
        return $requestMock;
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

        $controller = new TelegramController($routerMock, $serializerMock, $requestStackMock);
        $actualResponse = $controller->index();

        $this->assertSame($expectedResponse, $actualResponse);
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

        $controller = new TelegramController($routerMock, $serializerMock, $requestStackMock);
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

        $controller = new TelegramController($routerMock, $serializerMock, $requestStackMock);
        $actualResponse = $controller->index();
        
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'ignored']), 
            $actualResponse->getContent()
        );
    }
}
