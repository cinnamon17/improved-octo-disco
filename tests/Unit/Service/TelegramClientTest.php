<?php

namespace App\Tests\Unit\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
use App\Service\HttpService;
use App\Service\TelegramApiDtoFactory;
use App\Service\TelegramClient;
use App\Service\TelegramDtoFactory;
use App\Dto\TelegramBotUpdate;
use App\Service\TelegramHttpService;
use PHPUnit\Framework\TestCase;

class TelegramClientTest extends TestCase
{
    private TelegramHttpService $httpServiceMock;
    private TelegramApiDtoFactory $dtoFactoryMock;
    private TelegramBotUpdate $updateStub;
    private TelegramClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpServiceMock = $this->createMock(TelegramHttpService::class);
        $this->dtoFactoryMock = $this->createMock(TelegramApiDtoFactory::class);
        
        $this->updateStub = $this->createStub(TelegramBotUpdate::class);

        $this->client = new TelegramClient($this->httpServiceMock, $this->dtoFactoryMock);
    }

    public function testSendMessage(): void
    {
        $testMessage = 'Mensaje de prueba';
        $expectedDto = new TelegramMessageDto(); 
        $expectedApiResponse = ['ok' => true, 'result' => 'message_sent'];

        $this->dtoFactoryMock->expects($this->once())
            ->method('createSendMessageParams')
            ->with($testMessage, $this->updateStub)
            ->willReturn($expectedDto);

        $this->httpServiceMock->expects($this->once())
            ->method('telegramRequest')
            ->with($expectedDto)
            ->willReturn($expectedApiResponse);

        $actualResponse = $this->client->sendMessage($testMessage, $this->updateStub);

        $this->assertSame($expectedApiResponse, $actualResponse, 'Debe devolver la respuesta exacta del HttpService.');
    }
    
    public function testSendAdminMessageFromUpdate(): void
    {
        $expectedDto = new TelegramMessageDto();
        $expectedApiResponse = ['ok' => true, 'result' => 'admin_notified'];

        $this->dtoFactoryMock->expects($this->once())
            ->method('createAdminSendMessageParams')
            ->with($this->updateStub) 
            ->willReturn($expectedDto);

        $this->httpServiceMock->expects($this->once())
            ->method('telegramRequest')
            ->with($expectedDto)
            ->willReturn($expectedApiResponse);

        $actualResponse = $this->client->sendAdminMessageFromUpdate($this->updateStub);

        $this->assertSame($expectedApiResponse, $actualResponse);
    }

    public function testSendChatAction(): void
    {
        $testAction = 'typing';
        $expectedDto = new TelegramActionDto();
        $expectedApiResponse = ['ok' => true];

        $this->dtoFactoryMock->expects($this->once())
            ->method('createSendChatActionParams')
            ->with($testAction, $this->updateStub) 
            ->willReturn($expectedDto);

        $this->httpServiceMock->expects($this->once())
            ->method('telegramRequest')
            ->with($expectedDto)
            ->willReturn($expectedApiResponse);

        $actualResponse = $this->client->sendChatAction($testAction, $this->updateStub);

        $this->assertSame($expectedApiResponse, $actualResponse);
    }

    public function testAnswerCallbackQuery(): void
    {
        $expectedDto = new AnswerCallbackQueryDto();
        $expectedApiResponse = ['ok' => true];

        $this->dtoFactoryMock->expects($this->once())
            ->method('createAnswerCallbackQueryParams')
            ->with($this->updateStub) 
            ->willReturn($expectedDto);

        $this->httpServiceMock->expects($this->once())
            ->method('telegramRequest')
            ->with($expectedDto)
            ->willReturn($expectedApiResponse);

        $actualResponse = $this->client->answerCallbackQuery($this->updateStub);

        $this->assertSame($expectedApiResponse, $actualResponse);
    }

    public function testSendInlineKeyboard(): void
    {
        $expectedDto = new TelegramMessageDto();
        $expectedApiResponse = ['ok' => true, 'result' => 'keyboard_sent'];

        $this->dtoFactoryMock->expects($this->once())
            ->method('createSendInlineKeyboardParams')
            ->with($this->updateStub) 
            ->willReturn($expectedDto);

        $this->httpServiceMock->expects($this->once())
            ->method('telegramRequest')
            ->with($expectedDto)
            ->willReturn($expectedApiResponse);

        $actualResponse = $this->client->sendInlineKeyboard($this->updateStub);

        $this->assertSame($expectedApiResponse, $actualResponse);
    }
}

