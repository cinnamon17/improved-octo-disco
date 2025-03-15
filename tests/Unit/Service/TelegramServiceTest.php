<?php

namespace App\Tests\Unit\Service;

use App\Dto\CallbackQueryDto;
use App\Entity\Prompt;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\HttpService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramDtoFactory;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TelegramServiceTest extends TestCase
{
    private HttpService $httpService;
    private BotUpdateTranslator $bt;
    private DBService $dbService;
    private TelegramDtoFactory $dtoFactory;
    private TelegramBotUpdate $update;

    protected function setUp(): void
    {
        $this->httpService = $this->createStub(HttpService::class);
        $this->bt = $this->createStub(BotUpdateTranslator::class);
        $this->dbService = $this->createStub(DBService::class);
        $this->dtoFactory = $this->createStub(TelegramDtoFactory::class);
        $this->update = $this->createStub(TelegramBotUpdate::class);
    }
    public function testSetLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService, $this->dbService, $this->dtoFactory, $this->bt);
        $telegramService->setLogger($logger);
        $telegramService->log("hello");
    }

    public function testTelegramRequest(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->dbService, $this->dtoFactory, $this->bt);
        $response = $telegramService->telegramRequest(['params']);

        $this->assertIsArray($response);
    }

    public function testHandleCallbackQuery(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getCallbackQueryId')
            ->willReturn('test');

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $response = $telegramService->handleCallbackQuery();
        $this->assertIsArray($response);
    }

    public function testSendMessage(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->sendMessage('hello'));
    }

    public function testSendChatAction(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->sendChatAction('action'));
    }

    public function testAnswerCallbackQuery(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->answerCallbackQuery());
    }

    public function testSendInlineKeyboard(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->sendInlineKeyboard());
    }

    public function testSendWelcomeMessage(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->sendWelcomeMessage());
    }

    public function testChatCompletion(): void
    {

        $prompt = new Prompt();
        $prompt->setRole('doctor');

        $this->httpService->method('request')
            ->willReturn([]);

        $this->httpService->method('chatCompletion')
            ->willReturn([]);

        $this->dbService->method('getPrompt')
            ->willReturn($prompt);

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $this->assertIsArray($telegramService->chatCompletion('test'));
    }

    public function testLog(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService,  $this->dbService, $this->dtoFactory, $this->bt);
        $telegramService->setLogger($logger);
        $telegramService->log('testlog');
    }

    public function testSetBotMode(): void
    {

        $dbService = $this->createMock(DBService::class);
        $dbService->expects($this->once())
            ->method('updateUserMode');

        $telegramService = new TelegramService($this->httpService,  $dbService, $this->dtoFactory, $this->bt);
        $telegramService->setBotMode();
    }

    public function testIsUserExists(): void
    {

        $dbService = $this->createStub(DBService::class);
        $dbService
            ->method('isUserExists')
            ->willReturn(true);

        $telegramService = new TelegramService($this->httpService,  $dbService, $this->dtoFactory, $this->bt);
        $bool = $telegramService->isUserExists();

        $this->assertTrue($bool);
    }

    public function testSetInserUserInDb(): void
    {

        $dbService = $this->createMock(DBService::class);
        $dbService->expects($this->once())
            ->method('insertUserInDb');

        $telegramService = new TelegramService($this->httpService,  $dbService, $this->dtoFactory, $this->bt);
        $telegramService->insertUserInDb();
    }
}
