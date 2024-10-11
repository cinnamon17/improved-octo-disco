<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TelegramControllerTest extends TestCase
{
    public function testHandleCallbackQuery(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('isCallbackQuery')->willReturn(true);
        $telegramService->method('handleCallbackQuery')->willReturn([]);
        $telegramService->expects($this->once())->method('handleCallbackQuery');

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testChatIdIsNullReturnMessage(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('getChatId')->willReturn(null);

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $response = $controller->index();

        $this->assertEquals(json_encode('invalid chat_id'), $response->getContent());
    }

    public function testMesageTextNullReturnMessage(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('getChatId')->willReturn((float) 11111111);
        $telegramService->method('getMessageText')->willReturn(null);

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $response = $controller->index();

        $this->assertEquals(json_encode('invalid message'), $response->getContent());
    }

    public function testInsertUserInDb(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('isUserExists')->willReturn(false);
        $telegramService->method('getChatId')->willReturn((float) 11111111);
        $telegramService->method('getMessageText')->willReturn('hello');
        $telegramService->method('chatCompletion')->willReturn(["choices" => [0 => ["message" => ["content" => "hello"]]]]);
        $telegramService->expects($this->once())->method('insertUserInDb');

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testUpdateUserInDb(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('isUserExists')->willReturn(true);
        $telegramService->method('getChatId')->willReturn((float) 11111111);
        $telegramService->method('getMessageText')->willReturn('hello');
        $telegramService->method('chatCompletion')->willReturn(["choices" => [0 => ["message" => ["content" => "hello"]]]]);
        $telegramService->expects($this->once())->method('updateUserInDb');

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testStartOption(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('isUserExists')->willReturn(true);
        $telegramService->method('getChatId')->willReturn((float) 11111111);
        $telegramService->method('getMessageText')->willReturn('/start');
        $telegramService->expects($this->once())->method('sendWelcomeMessage');

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testModeOption(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $telegramService->method('isUserExists')->willReturn(true);
        $telegramService->method('getChatId')->willReturn((float) 11111111);
        $telegramService->method('getMessageText')->willReturn('/mode');
        $telegramService->expects($this->once())->method('sendInlineKeyboard');

        $controller = new TelegramController($telegramService);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }
}
