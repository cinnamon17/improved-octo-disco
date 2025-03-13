<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ChatPromptMessageDto;
use App\Dto\UpdateDto;
use App\Entity\Prompt;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramDtoFactory;
use PHPUnit\Framework\TestCase;

class TelegramDtoFactoryTest extends TestCase
{
    private $botUpdateTranslator;
    private $telegramDtoFactory;
    private $telegramBotUpdate;

    protected function setUp(): void
    {
        $this->botUpdateTranslator = $this->createStub(BotUpdateTranslator::class);
        $this->telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $this->telegramDtoFactory = new TelegramDtoFactory($this->botUpdateTranslator, $this->telegramBotUpdate);
    }

    public function testCreateCallbackQueryParams(): void
    {
        $translatedMessage = 'Callback query message';

        $this->botUpdateTranslator->method('translate')
            ->with('callbackQuery.message')
            ->willReturn($translatedMessage);

        $expectedParams = [
            'method' => 'sendMessage',
            'chat_id' => $this->telegramBotUpdate->getCallbackQueryChatId(),
            'text' => $translatedMessage,
            'reply_markup' => ''
        ];

        $result = $this->telegramDtoFactory->createCallbackQueryParams();

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendMessageParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $message = 'Hello, world!';

        $expectedParams = [
            'chat_id' => $this->telegramBotUpdate->getChatId(),
            'method' => 'sendMessage',
            'text' => $message,
            'reply_markup' => ''
        ];

        $result = $this->telegramDtoFactory->createSendMessageParams($message);

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendChatActionParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $action = 'typing';

        $expectedParams = [
            'chat_id' => $this->telegramBotUpdate->getChatId(),
            'method' => 'sendChatAction',
            'action' => $action,
        ];

        $result = $this->telegramDtoFactory->createSendChatActionParams($action);

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateAnswerCallbackQueryParams(): void
    {

        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $expectedParams = [
            'callback_query_id' => $this->telegramBotUpdate->getCallbackQueryId(),
            'method' => 'answerCallbackQuery',
        ];

        $result = $this->telegramDtoFactory->createAnswerCallbackQueryParams();

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendInlineKeyboardParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $translatorMessage = 'Translate this';
        $assistantMessage = 'Assist me';
        $bussinessMessage = 'Startup idea';

        $this->botUpdateTranslator->method('getTranslatorMessage')
            ->willReturn($translatorMessage);
        $this->botUpdateTranslator->method('getAssistantMessage')
            ->willReturn($assistantMessage);
        $this->botUpdateTranslator->method('getBusinessMessage')
            ->willReturn($bussinessMessage);
        $this->botUpdateTranslator->method('getCharacterMessage')
            ->willReturn('Choose a character');

        $expectedParams = [
            'method' => 'sendMessage',
            'chat_id' => $this->telegramBotUpdate->getChatId(),
            'text' => 'Choose a character',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'Translate this 🈯', 'callback_data' => 'Translate this'],
                        ['text' => 'Assist me 👨🏻‍🏫', 'callback_data' => 'Assist me'],
                    ],
                    [
                        ['text' => 'chef 🧑🏻‍🍳', 'callback_data' => 'chef'],
                        ['text' => 'doctor 👨🏻‍⚕️', 'callback_data' => 'doctor'],
                    ],
                    [
                        ['text' => 'Startup idea💡', 'callback_data' => 'startup'],
                    ],
                ],
            ],
        ];

        $result = $this->telegramDtoFactory->createSendInlineKeyboardParams();

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateChatPromptMessageDto(): void
    {

        $db = $this->createStub(DBService::class);
        $prompt = $this->createStub(Prompt::class);
        $prompt
            ->method('getRole')
            ->willReturn('test');

        $this->telegramBotUpdate
            ->method('getMessageText')
            ->willReturn('test');

        $db->method('getPrompt')
            ->willReturn($prompt);

        $result = $this->telegramDtoFactory->createChatPromptMessageDto($db);
        $this->assertInstanceOf(ChatPromptMessageDto::class, $result);
    }
}
