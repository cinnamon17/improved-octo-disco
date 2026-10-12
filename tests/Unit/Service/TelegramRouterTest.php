<?php

namespace App\Tests\Unit\Service;

use App\Service\TelegramRouter;
use App\Service\TelegramService;
use App\Service\TelegramPaymentService;
use App\Dto\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramRouterTest extends TestCase
{
    private TelegramService $telegramServiceStub;
    private TelegramPaymentService $paymentServiceStub;
    private TelegramRouter $router;

    protected function setUp(): void
    {
        $this->telegramServiceStub = $this->createMock(TelegramService::class);
        $this->paymentServiceStub = $this->createMock(TelegramPaymentService::class);
        $this->router = new TelegramRouter($this->telegramServiceStub, $this->paymentServiceStub);
    }

    /**
     * Todas las ramas de pago se comprueban antes que la de callback y antes
     * que getMessageText: esos updates no traen message ni text, y llamarlos
     * reventaria con Error.
     */
    private function mockUpdateWithoutText(): TelegramBotUpdate
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->method('isPreCheckoutQuery')->willReturn(false);
        $updateMock->method('hasSuccessfulPayment')->willReturn(false);
        $updateMock->method('hasRefundedPayment')->willReturn(false);

        return $updateMock;
    }

    public function testHandleCallbackQuery(): void
    {
        $expectedResponse = ['status' => 'callback handled'];

        $updateMock = $this->mockUpdateWithoutText();

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

    /**
     * El orden de ramas es el requisito: pre_checkout_query no trae message,
     * asi que si se evaluara getMessageText() primero, petaria.
     */
    public function testPreCheckoutQueryIsHandledBeforeMessageAccess(): void
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->expects($this->once())
                   ->method('isPreCheckoutQuery')
                   ->willReturn(true);

        $updateMock->expects($this->never())->method('isCallbackQuery');
        $updateMock->expects($this->never())->method('getMessageText');

        $this->paymentServiceStub->expects($this->once())
                                  ->method('handlePreCheckoutQuery')
                                  ->with($updateMock)
                                  ->willReturn(['status' => 'pre_checkout_accepted']);

        $this->telegramServiceStub->expects($this->never())->method('handleIncomingMessage');

        $actualResponse = $this->router->handle($updateMock);

        $this->assertSame(
            ['status' => 'pre_checkout_accepted'],
            json_decode($actualResponse->getContent(), true)
        );
    }

    public function testSuccessfulPaymentIsCredited(): void
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->expects($this->once())->method('isPreCheckoutQuery')->willReturn(false);
        $updateMock->expects($this->once())->method('hasSuccessfulPayment')->willReturn(true);
        $updateMock->expects($this->never())->method('getMessageText');

        $this->paymentServiceStub->expects($this->once())
                                  ->method('handleSuccessfulPayment')
                                  ->with($updateMock)
                                  ->willReturn(['status' => 'payment_credited']);

        $actualResponse = $this->router->handle($updateMock);

        $this->assertSame(
            ['status' => 'payment_credited'],
            json_decode($actualResponse->getContent(), true)
        );
    }

    public function testRefundedPaymentIsProcessed(): void
    {
        $updateMock = $this->createMock(TelegramBotUpdate::class);

        $updateMock->method('isPreCheckoutQuery')->willReturn(false);
        $updateMock->method('hasSuccessfulPayment')->willReturn(false);
        $updateMock->method('hasRefundedPayment')->willReturn(true);
        $updateMock->expects($this->never())->method('getMessageText');

        $this->paymentServiceStub->expects($this->once())
                                  ->method('handleRefundedPayment')
                                  ->with($updateMock)
                                  ->willReturn(['status' => 'refunded']);

        $actualResponse = $this->router->handle($updateMock);

        $this->assertSame(['status' => 'refunded'], json_decode($actualResponse->getContent(), true));
    }

    public function testHandleValidMessage(): void
    {
        $expectedResponse = ['status' => 'message handled'];

        $updateMock = $this->mockUpdateWithoutText();

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
        $updateMock = $this->mockUpdateWithoutText();

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
        $updateMock = $this->mockUpdateWithoutText();

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
        $this->assertEquals(200, $actualResponse->getStatusCode());
    }
}