<?php

namespace App\Tests\Unit\Service;

use App\Dto\TelegramMessageDto;
use App\Service\TelegramHttpService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

/**
 * Telegram responde siempre 200, con ok=false y la causa en description.
 * Sin loguearlo, un sendInvoice fallido deja una StarTransaction en PENDING
 * que el pre_checkout_query daria por valida.
 */
class TelegramHttpServiceLoggingTest extends TestCase
{
    private function createService(array $body, LoggerInterface $logger): TelegramHttpService
    {
        $env = $this->createStub(ContainerBagInterface::class);
        $env->method('get')->willReturn('https://api.telegram.org/botTOKEN/');

        $client = new MockHttpClient(new JsonMockResponse($body));

        $service = new TelegramHttpService($client, $env);
        $service->setLogger($logger);

        return $service;
    }

    private function dto(): TelegramMessageDto
    {
        return (new TelegramMessageDto())
            ->setMethod('sendInvoice')
            ->setChatId(11111111)
            ->setText('Recarga');
    }

    public function testFailedResponseIsLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                'Telegram API devolvio ok=false',
                $this->callback(function (array $context): bool {
                    return $context['method'] === 'sendInvoice'
                        && $context['error_code'] === 400
                        && $context['description'] === 'Bad Request: PROVIDER_ACCOUNT_INVALID';
                })
            );

        $service = $this->createService([
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: PROVIDER_ACCOUNT_INVALID',
        ], $logger);

        $service->telegramRequest($this->dto());
    }

    public function testFailedResponseIncludesTheRequestPayload(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->anything(),
                $this->callback(function (array $context): bool {
                    // El payload del request viaja en el log para poder
                    // reproducir la llamada sin instrumentar el cliente.
                    return isset($context['request']['chat_id'])
                        && $context['request']['chat_id'] === 11111111;
                })
            );

        $service = $this->createService([
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: not found',
        ], $logger);

        $service->telegramRequest($this->dto());
    }

    public function testSuccessfulResponseIsNotLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $service = $this->createService([
            'ok' => true,
            'result' => ['message_id' => 1],
        ], $logger);

        $service->telegramRequest($this->dto());
    }

    public function testResponseIsStillReturnedToTheCaller(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $service = $this->createService([
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: chat not found',
        ], $logger);

        $body = $service->telegramRequest($this->dto());

        // El log no sustituye al retorno: createInvoice necesita leer el ok.
        $this->assertFalse($body['ok']);
        $this->assertSame('Bad Request: chat not found', $body['description']);
    }

    public function testResponseWithoutOkFieldIsTreatedAsSuccess(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        // defensivo: si Telegram devolviera algo sin 'ok', no queremos
        // marcar FAILED una factura que si se envio
        $service = $this->createService(['result' => ['message_id' => 1]], $logger);

        $this->assertIsArray($service->telegramRequest($this->dto()));
    }
}