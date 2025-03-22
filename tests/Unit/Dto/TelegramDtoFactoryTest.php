<?php

namespace App\Tests\Unit\Dto;

use App\Dto\CallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\UpdateDto;
use App\Dto\UserDto;
use App\Entity\Message;
use App\Entity\Prompt;
use App\Entity\User;
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

    public function testCreateAdminSendMessageParams(): void
    {

        $this->telegramBotUpdate
            ->method('getMessageText')
            ->willReturn('Hello, world!');

        $message = 'Hello, world!';
        $expectedParams = [
            'chat_id' => 1136298813,
            'method' => 'sendMessage',
            'text' => $message,
            'reply_markup' => ''
        ];

        $result = $this->telegramDtoFactory->createAdminSendMessageParams();
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

        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $this->telegramBotUpdate
            ->method('getIsBot')
            ->willReturn(true);

        $this->botUpdateTranslator
            ->method('getAssistantMessage')
            ->willReturn('assistant');

        $this->telegramBotUpdate
            ->method('getFirstName')
            ->willReturn('test');

        $this->telegramBotUpdate
            ->method('getLocale')
            ->willReturn('es');

        $db->method('getPrompt')
            ->willReturn($prompt);

        $result = $this->telegramDtoFactory->createChatPromptMessageDto($db);
        $this->assertInstanceOf(ChatPromptMessageDto::class, $result);
    }

    public function testCreateUserBotMode(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(1234);

        $this->telegramBotUpdate
            ->method('getIsBot')
            ->willReturn(true);

        $this->telegramBotUpdate
            ->method('getFirstName')
            ->willReturn('test');

        $this->botUpdateTranslator
            ->method('getAssistantMessage')
            ->willReturn('assistant');

        $user = $this->telegramDtoFactory->createUserBotMode();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testCreateUser(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $this->telegramBotUpdate
            ->method('getIsBot')
            ->willReturn(true);

        $this->botUpdateTranslator
            ->method('getAssistantMessage')
            ->willReturn('assistant');

        $this->telegramBotUpdate
            ->method('getFirstName')
            ->willReturn('test');

        $user = $this->telegramDtoFactory->createUser();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testCreateMessage(): void
    {
        $this->telegramBotUpdate
            ->method('getMessageText')
            ->willReturn('test');

        $this->telegramBotUpdate
            ->method('getMessageId')
            ->willReturn(12345);

        $message = $this->telegramDtoFactory->createMessage();
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testCreateChatId(): void
    {
        $callbackQueryDto = $this->createStub(CallbackQueryDto::class);
        $userDto = $this->createStub(UserDto::class);
        $userDto
            ->method('getId')
            ->willReturn(123456);

        $callbackQueryDto
            ->method('getFrom')
            ->willReturn($userDto);

        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(123456);

        $this->telegramBotUpdate
            ->method('getCallbackQuery')
            ->willReturn($callbackQueryDto);

        $chatId = $this->telegramDtoFactory->createChatIdFromUpdate();
        $this->assertEquals(123456, $chatId);
    }
}
