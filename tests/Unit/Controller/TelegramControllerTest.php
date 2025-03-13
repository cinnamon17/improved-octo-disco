<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TelegramController;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TelegramControllerTest extends TestCase
{
    public function testHandleCallbackQuery(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);

        $update->method('isCallbackQuery')->willReturn(true);
        $telegramService->method('handleCallbackQuery')->willReturn([]);
        $telegramService->expects($this->once())->method('handleCallbackQuery');

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testChatIdIsNullReturnMessage(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $update->method('getChatId')->willReturn(null);

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $response = $controller->index();

        $this->assertEquals(json_encode('invalid chat_id'), $response->getContent());
    }

    public function testMesageTextNullReturnMessage(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $update->method('getChatId')->willReturn((int) 11111111);
        $update->method('getMessageText')->willReturn(null);

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $response = $controller->index();

        $this->assertEquals(json_encode('invalid message'), $response->getContent());
    }

    public function testInsertUserInDb(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $db->method('isUserExists')->willReturn(false);
        $update->method('getChatId')->willReturn((int) 11111111);
        $update->method('getMessageText')->willReturn('hello');
        $telegramService->method('chatCompletion')->willReturn(["choices" => [0 => ["message" => ["content" => "hello"]]]]);
        $db->expects($this->once())->method('insertUserInDb');

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testUpdateUserInDb(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $db->method('isUserExists')->willReturn(true);
        $update->method('getChatId')->willReturn((int) 11111111);
        $update->method('getMessageText')->willReturn('hello');
        $telegramService->method('chatCompletion')->willReturn(["choices" => [0 => ["message" => ["content" => "hello"]]]]);
        $db->expects($this->once())->method('updateUserInDb');

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testStartOption(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $db->method('isUserExists')->willReturn(true);
        $update->method('getChatId')->willReturn((int) 11111111);
        $update->method('getMessageText')->willReturn('/start');
        $telegramService->expects($this->once())->method('sendWelcomeMessage');

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }

    public function testModeOption(): void
    {

        $telegramService = $this->createMock(TelegramService::class);
        $update = $this->createMock(TelegramBotUpdate::class);
        $db = $this->createMock(DBService::class);
        $db->method('isUserExists')->willReturn(true);
        $update->method('getChatId')->willReturn((int) 11111111);
        $update->method('getMessageText')->willReturn('/mode');
        $telegramService->expects($this->once())->method('sendInlineKeyboard');

        $controller = new TelegramController($telegramService, $update, $db);
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $controller->index();
    }
}
