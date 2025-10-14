<?php

namespace App\Tests\Unit\Dto;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\CallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\OpenAIDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
use App\Dto\UserDto;
use App\Entity\Message;
use App\Entity\Prompt;
use App\Entity\User;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramDtoFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TelegramDtoFactoryTest extends TestCase
{
    private $botUpdateTranslator;
    private $telegramDtoFactory;
    private $telegramBotUpdate;
    private $chatPromptMessageDto;
    private $env;

    protected function setUp(): void
    {
        $this->botUpdateTranslator = $this->createStub(BotUpdateTranslator::class);
        $this->telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $this->env = $this->createStub(ContainerBagInterface::class);
        $this->chatPromptMessageDto = $this->createStub(ChatPromptMessageDto::class);
        $this->telegramDtoFactory = new TelegramDtoFactory($this->botUpdateTranslator, $this->telegramBotUpdate, $this->env);
    }

    public function testCreateCallbackQueryParams(): void
    {
        $result = $this->telegramDtoFactory->createCallbackQueryParams();
        $this->assertInstanceOf(TelegramMessageDto::class, $result);
    }

    public function testCreateSendMessageParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(1111111111);

        $message = 'Hello, world!';
        $result = $this->telegramDtoFactory->createSendMessageParams($message);

        $this->assertInstanceOf(TelegramMessageDto::class, $result);
    }

    public function testCreateAdminSendMessageParams(): void
    {
        $this->telegramBotUpdate
            ->method('getMessageText')
            ->willReturn('test');

        $result = $this->telegramDtoFactory->createAdminSendMessageParams();
        $this->assertInstanceOf(TelegramMessageDto::class, $result);
    }

    public function testCreateSendChatActionParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(1111111111);

        $action = 'typing';
        $result = $this->telegramDtoFactory->createSendChatActionParams($action);

        $this->assertInstanceOf(TelegramActionDto::class, $result);
    }

    public function testCreateAnswerCallbackQueryParams(): void
    {
        $result = $this->telegramDtoFactory->createAnswerCallbackQueryParams();
        $this->assertInstanceOf(AnswerCallbackQueryDto::class, $result);
    }

    public function testCreateSendInlineKeyboardParams(): void
    {
        $this->telegramBotUpdate
            ->method('getChatId')
            ->willReturn(1111111111);

        $result = $this->telegramDtoFactory->createSendInlineKeyboardParams();
        $this->assertInstanceOf(TelegramMessageDto::class, $result);
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

    public function testCreateRequestAIparams(): void
    {
	$this->env
	    ->method('get')
	    ->with('OPENAI_KEY')
	    ->willReturn('test');

	$this->chatPromptMessageDto
	    ->method('getPrompt')
	    ->willReturn('prompt');

	$this->chatPromptMessageDto
	    ->method('getMessage')
	    ->willReturn('message');

        $dto = $this->telegramDtoFactory->createRequestAIparams($this->chatPromptMessageDto);
        $this->assertInstanceOf(OpenAIDto::class, $dto);
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
