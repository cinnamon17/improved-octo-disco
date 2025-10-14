<?php

namespace App\Tests\Unit\Service;

use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\HttpService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramDtoFactory;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;

class TelegramServiceTest extends TestCase
{
    private HttpService $httpService;
    private BotUpdateTranslator $bt;
    private DBService $dbService;
    private TelegramDtoFactory $dtoFactory;
    private TelegramBotUpdate $update;
    private MessageBusInterface $dispatcher;

    protected function setUp(): void
    {
        $this->httpService = $this->createStub(HttpService::class);
        $this->bt = $this->createStub(BotUpdateTranslator::class);
        $this->dbService = $this->createStub(DBService::class);
        $this->dtoFactory = $this->createStub(TelegramDtoFactory::class);
        $this->update = $this->createStub(TelegramBotUpdate::class);
        $this->dispatcher = $this->createStub(MessageBusInterface::class);
    }
    public function testSetLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService, $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $telegramService->setLogger($logger);
        $telegramService->log("hello");
    }

    public function testHandleCallbackQuery(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getCallbackQueryId')
            ->willReturn('test');

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $response = $telegramService->handleCallbackQuery();
        $this->assertIsArray($response);
    }

    public function testSendMessage(): void
    {

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $this->assertIsArray($telegramService->sendMessage('hello'));
    }

    public function testSendChatAction(): void
    {

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $this->assertIsArray($telegramService->sendChatAction('action'));
    }

    public function testAnswerCallbackQuery(): void
    {

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $this->assertIsArray($telegramService->answerCallbackQuery());
    }

    public function testSendInlineKeyboard(): void
    {

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $this->assertInstanceOf(JsonResponse::class,$telegramService->sendInlineKeyboard());
    }

    public function testSendWelcomeMessage(): void
    {

        $this->httpService->method('telegramRequest')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $this->assertInstanceOf(JsonResponse::class,$telegramService->sendWelcomeMessage());
    }

    public function testLog(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $telegramService->setLogger($logger);
        $telegramService->log('testlog');
    }

    public function testSetBotMode(): void
    {

        $dbService = $this->createMock(DBService::class);
        $dbService->expects($this->once())
            ->method('updateUserMode');

        $telegramService = new TelegramService($this->httpService,  $dbService, $this->dtoFactory, $this->bt, $this->dispatcher);
        $telegramService->setBotMode();
    }
}
