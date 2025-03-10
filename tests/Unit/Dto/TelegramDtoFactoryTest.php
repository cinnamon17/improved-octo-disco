<?php

namespace App\Tests\Unit\Dto;

use App\Service\BotUpdateTranslator;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramDtoFactory;
use PHPUnit\Framework\TestCase;

class TelegramDtoFactoryTest extends TestCase
{
    private $botUpdateTranslator;
    private $telegramDtoFactory;

    protected function setUp(): void
    {
        $this->botUpdateTranslator = $this->createStub(BotUpdateTranslator::class);
        $this->telegramDtoFactory = new TelegramDtoFactory($this->botUpdateTranslator);
    }

    public function testCreateCallbackQueryParams(): void
    {
        $translatedMessage = 'Callback query message';

        $this->botUpdateTranslator->method('translate')
            ->with('callbackQuery.message')
            ->willReturn($translatedMessage);

        $expectedParams = [
            'method' => 'sendMessage',
            'chat_id' => $this->botUpdateTranslator->update()->getCallbackQueryChatId(),
            'text' => $translatedMessage,
            'reply_markup' => ''
        ];

        $result = $this->telegramDtoFactory->createCallbackQueryParams();

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendMessageParams(): void
    {
        $message = 'Hello, world!';

        $expectedParams = [
            'chat_id' => $this->botUpdateTranslator->update()->getChatId(),
            'method' => 'sendMessage',
            'text' => $message,
            'reply_markup' => ''
        ];

        $result = $this->telegramDtoFactory->createSendMessageParams($message);

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendChatActionParams(): void
    {
        $action = 'typing';

        $expectedParams = [
            'chat_id' => $this->botUpdateTranslator->update()->getChatId(),
            'method' => 'sendChatAction',
            'action' => $action,
        ];

        $result = $this->telegramDtoFactory->createSendChatActionParams($action);

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateAnswerCallbackQueryParams(): void
    {

        $expectedParams = [
            'callback_query_id' => $this->botUpdateTranslator->update()->getCallbackQueryId(),
            'method' => 'answerCallbackQuery',
        ];

        $result = $this->telegramDtoFactory->createAnswerCallbackQueryParams();

        $this->assertEquals($expectedParams, $result);
    }

    public function testCreateSendInlineKeyboardParams(): void
    {
        $chatId = 12345;
        $translatorMessage = 'Translate this';
        $assistantMessage = 'Assist me';
        $bussinessMessage = 'Startup idea';

        $this->botUpdateTranslator->method('getTranslatorMessage')
            ->willReturn($translatorMessage);
        $this->botUpdateTranslator->method('getAssistantMessage')
            ->willReturn($assistantMessage);
        $this->botUpdateTranslator->method('getbussinessMessage')
            ->willReturn($bussinessMessage);
        $this->botUpdateTranslator->method('getCharacterMessage')
            ->willReturn('Choose a character');

        $expectedParams = [
            'method' => 'sendMessage',
            'chat_id' => $this->botUpdateTranslator->update()->getChatId(),
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
}
