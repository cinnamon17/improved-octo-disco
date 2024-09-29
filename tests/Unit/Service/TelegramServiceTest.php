<?php

namespace App\Tests\Unit\Service;

use App\Entity\Prompt;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\HttpService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TelegramServiceTest extends TestCase
{
    private HttpService $httpService;
    private BotUpdateTranslator $bt;
    private DBService $dbService;

    protected function setUp(): void
    {
        $this->httpService = $this->createStub(HttpService::class);
        $this->bt = $this->createStub(BotUpdateTranslator::class);
        $this->dbService = $this->createStub(DBService::class);
    }
    public function testSetLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $telegramService->setLogger($logger);
        $telegramService->log("hello");
    }

    public function testTelegramRequest(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $response = $telegramService->telegramRequest(['params']);

        $this->assertIsArray($response);
    }

    public function testHandleCallbackQuery(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getCallbackQuery')
            ->willReturn(['id' => 'test']);

        $this->httpService->method('request')
            ->willReturn([]);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $response = $telegramService->handleCallbackQuery();
        $this->assertIsArray($response);
    }

    public function testSendMessage(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->sendMessage('hello'));
    }

    public function testSendChatAction(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->sendChatAction('action'));
    }

    public function testAnswerCallbackQuery(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->answerCallbackQuery());
    }

    public function testSendInlineKeyboard(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->sendInlineKeyboard());
    }

    public function testSendWelcomeMessage(): void
    {

        $this->httpService->method('request')
            ->willReturn([]);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->sendWelcomeMessage());
    }

    public function testIsUserExists(): void
    {

        $this->dbService->method('isUserExists')
            ->willReturn(true);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsBool($telegramService->isUserExists());
    }

    public function testIsCallbackQuery(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getCallbackQuery')
            ->willReturn(['id' => 'test']);

        $this->httpService->method('request')
            ->willReturn([]);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsBool($telegramService->isCallbackQuery());
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

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertIsArray($telegramService->chatCompletion('test'));
    }

    public function testSetBotMode(): void
    {

        $dbService = $this->createMock(DBService::class);

        $dbService->expects($this->once())
            ->method('setBotMode');

        $telegramService = new TelegramService($this->httpService, $this->bt, $dbService);
        $telegramService->setBotMode();
    }

    public function testTranslate(): void
    {
        $this->bt->method('translate')
            ->willReturn('hello');

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('hello', $telegramService->translate('test'));
    }

    public function testLog(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info');

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $telegramService->setLogger($logger);
        $telegramService->log('testlog');
    }

    public function testGetChatId(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals(12345, $telegramService->getChatId());
    }

    public function testGetFistName(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getFirstName')
            ->willReturn('name');

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('name', $telegramService->getFirstName());
    }

    public function testGetLastName(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLastName')
            ->willReturn('lastname');

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('lastname', $telegramService->getLastname());
    }

    public function testGetUsername(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getUsername')
            ->willReturn('username');

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('username', $telegramService->getUsername());
    }

    public function testGetMessageId(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getMessageId')
            ->willReturn((float) 12345);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals((float) 12345, $telegramService->getMessageId());
    }

    public function testGetMessageText(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getMessageText')
            ->willReturn('hello');

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('hello', $telegramService->getMessageText());
    }

    public function testGetCallbackQueryId(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getCallbackQuery')
            ->with('id')
            ->willReturn(12345);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals(12345, $telegramService->getCallbackQueryId());
    }

    public function testGetCallbackQueryChatId(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getCallbackQuery')
            ->willReturn(['id' => '12345']);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('12345', $telegramService->getCallbackQueryId()['id']);
    }

    public function testGetCallbackQueryData(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getCallbackQuery')
            ->with('data')
            ->willReturn([]);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals([], $telegramService->getCallbackQueryData());
    }

    public function testGetLanguageCode(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertEquals('es', $telegramService->getLanguageCode());
    }

    public function testGetIsBot(): void
    {
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $this->bt->method('update')
            ->willReturn($telegramBotUpdate);

        $telegramService = new TelegramService($this->httpService, $this->bt, $this->dbService);
        $this->assertTrue($telegramService->getIsBot());
    }

    public function testInsertUserInDb(): void
    {
        $dbService = $this->createMock(DBService::class);

        $dbService->expects($this->once())
            ->method('insertUserInDb');

        $telegramService = new TelegramService($this->httpService, $this->bt, $dbService);
        $telegramService->insertUserInDb();
    }
}
