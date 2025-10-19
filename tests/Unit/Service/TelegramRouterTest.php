<?php

namespace App\Tests\Unit\Service;

use App\Service\TelegramRouter;
use App\Service\TelegramService;
use App\Dto\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramRouterTest extends TestCase
{
    private TelegramService $telegramServiceStub;
    private TelegramRouter $router;

    protected function setUp(): void
    {
        $this->telegramServiceStub = $this->createMock(TelegramService::class);
        $this->router = new TelegramRouter($this->telegramServiceStub);
    }

    public function testHandleCallbackQuery(): void
    {

        $expectedResponse = ['status' => 'callback handled'];

        $updateMock = $this->createMock(TelegramBotUpdate::class);
        
        $updateMock->expects($this->once())
                   ->method('isCallbackQuery')
                   ->willReturn(true);

        $this->telegramServiceStub->expects($this->once())
                                  ->method('handleCallbackQuery')
				  ->with($updateMock)
                                  ->willReturn($expectedResponse);

        $actualResponse = $this->router->handle($updateMock);
        $this->assertSame($expectedResponse, json_decode($actualResponse->getContent(), true));
    }

    public function testHandleValidMessage(): void
    {
        $expectedResponse = ['status' => 'message handled'];

        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->expects($this->once())
                   ->method('isCallbackQuery')
                   ->willReturn(false);

        $updateMock->expects($this->once())
                   ->method('getChatId')
                   ->willReturn(12345);
        
        $updateMock->expects($this->once())
                   ->method('getMessageText')
                   ->willReturn('Hola Bot');

        $this->telegramServiceStub->expects($this->once())
                                  ->method('handleIncomingMessage')
                                  ->willReturn($expectedResponse);

        $actualResponse = $this->router->handle($updateMock);

        $this->assertSame($expectedResponse, json_decode($actualResponse->getContent(), true));
    }

    public function testHandleInvalidMessageMissingChatId(): void
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->expects($this->once())
                   ->method('isCallbackQuery')
                   ->willReturn(false);

        $updateMock->expects($this->once())
                   ->method('getChatId')
                   ->willReturn(null); 

        $this->telegramServiceStub->expects($this->never())
                                  ->method('handleIncomingMessage');
        
        $this->telegramServiceStub->expects($this->never())
                                  ->method('handleCallbackQuery');

        $actualResponse = $this->router->handle($updateMock);

        $this->assertInstanceOf(JsonResponse::class, $actualResponse);
        $this->assertEquals(['status' => 'ignored'], json_decode($actualResponse->getContent(), true));
        $this->assertEquals(200, $actualResponse->getStatusCode());
    }

    public function testHandleInvalidMessageMissingMessageText(): void
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->expects($this->once())
                   ->method('isCallbackQuery')
                   ->willReturn(false);

        $updateMock->expects($this->once())
                   ->method('getChatId')
                   ->willReturn(12345);
        
        $updateMock->expects($this->once())
                   ->method('getMessageText')
                   ->willReturn(null); 

        $this->telegramServiceStub->expects($this->never())
                                  ->method('handleIncomingMessage');

        $actualResponse = $this->router->handle($updateMock);

        $this->assertInstanceOf(JsonResponse::class, $actualResponse);
        $this->assertEquals(['status' => 'ignored'], json_decode($actualResponse->getContent(), true));
    }
}
