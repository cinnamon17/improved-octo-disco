<?php

namespace App\Tests\Unit\Dto;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
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
    private BotUpdateTranslator $btMock;
    private ContainerBagInterface $envMock;
    private TelegramDtoFactory $factory;
    private TelegramBotUpdate $updateMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->btMock = $this->createMock(BotUpdateTranslator::class);
        $this->envMock = $this->createMock(ContainerBagInterface::class);

        $this->factory = new TelegramDtoFactory($this->btMock, $this->envMock);

        $this->updateMock = $this->createMock(TelegramBotUpdate::class);
    }

    public function testCreateSendMessageParams(): void
    {
        $expectedChatId = 12345;
        $messageText = 'Hola, este es un mensaje.';

        $this->updateMock->method('getChatId')->willReturn($expectedChatId);

        $dto = $this->factory->createSendMessageParams($messageText, $this->updateMock);

        $this->assertInstanceOf(TelegramMessageDto::class, $dto);
        $this->assertEquals('sendMessage', $dto->getMethod());
        $this->assertEquals($expectedChatId, $dto->getChatId());
        $this->assertEquals($messageText, $dto->getText());
    }

    public function testCreateGenericSendMessageParams(): void
    {
        $expectedChatId = 98765;
        $messageText = 'Mensaje para otro chat.';

        $dto = $this->factory->createGenericSendMessageParams($messageText, $expectedChatId);

        $this->assertInstanceOf(TelegramMessageDto::class, $dto);
        $this->assertEquals('sendMessage', $dto->getMethod());
        $this->assertEquals($expectedChatId, $dto->getChatId());
        $this->assertEquals($messageText, $dto->getText());
    }

    public function testCreateCallbackQueryParams(): void
    {
        $expectedChatId = 112233;
        $expectedMessage = 'Modo de bot actualizado.';

        $this->updateMock->method('getCallbackQueryChatId')->willReturn($expectedChatId);
        $this->btMock->method('translate')
            ->with('callbackQuery.message')
            ->willReturn($expectedMessage);

        $dto = $this->factory->createCallbackQueryParams($this->updateMock);

        $this->assertInstanceOf(TelegramMessageDto::class, $dto);
        $this->assertEquals('sendMessage', $dto->getMethod());
        $this->assertEquals($expectedChatId, $dto->getChatId());
        $this->assertEquals($expectedMessage, $dto->getText());
    }

    public function testCreateSendChatActionParams(): void
    {
        $expectedChatId = 55555;
        $action = 'typing';

        $this->updateMock->method('getChatId')->willReturn($expectedChatId);

        $dto = $this->factory->createSendChatActionParams($action, $this->updateMock);

        $this->assertInstanceOf(TelegramActionDto::class, $dto);
        $this->assertEquals('sendChatAction', $dto->getMethod());
        $this->assertEquals($expectedChatId, $dto->getChatId());
        $this->assertEquals($action, $dto->getAction());
    }

    public function testCreateAnswerCallbackQueryParams(): void
    {
        $expectedId = 'ABC-123';
        $this->updateMock->method('getCallbackQueryId')->willReturn($expectedId);

        $dto = $this->factory->createAnswerCallbackQueryParams($this->updateMock);

        $this->assertInstanceOf(AnswerCallbackQueryDto::class, $dto);
        $this->assertEquals('answerCallbackQuery', $dto->getMethod());
        $this->assertEquals($expectedId, $dto->getId());
    }

    public function testCreateUserBotMode(): void
    {
        $expectedChatId = 77777;
        $expectedMode = 'role_chef';

        $this->updateMock->method('getCallbackQueryChatId')->willReturn($expectedChatId);
        $this->updateMock->method('getCallbackQueryData')->willReturn($expectedMode);

        $entity = $this->factory->createUserBotMode($this->updateMock);

        $this->assertInstanceOf(User::class, $entity);
        $this->assertEquals($expectedChatId, $entity->getChatId());
        $this->assertEquals($expectedMode, $entity->getMode());
    }

    public function testCreateUser(): void
    {
        $this->updateMock->method('getChatId')->willReturn(88888);
        $this->updateMock->method('getIsBot')->willReturn(false);
        $this->updateMock->method('getFirstName')->willReturn('Jane');
        $this->btMock->method('getAssistantMessage')->willReturn('default_assistant');

        $entity = $this->factory->createUser($this->updateMock);

        $this->assertInstanceOf(User::class, $entity);
        $this->assertEquals(88888, $entity->getChatId());
        $this->assertFalse($entity->getIsBot());
        $this->assertEquals('Jane', $entity->getFirstName());
        $this->assertEquals('default_assistant', $entity->getMode());
    }

    public function testCreateChatPromptMessageDto(): void
    {
        $dbMock = $this->createMock(DBService::class);
        $expectedRole = 'system_role';
        $expectedMessage = '¿Qué es PHP?';
        $expectedLocale = 'es';

        $prompt = new Prompt(); 
        $prompt->setRole($expectedRole);

        $this->updateMock->method('getChatId')->willReturn(88888);
        $this->updateMock->method('getIsBot')->willReturn(false);
        $this->updateMock->method('getFirstName')->willReturn('Jane');
        $this->updateMock->method('getMessageText')->willReturn($expectedMessage);
        $this->updateMock->method('getLocale')->willReturn($expectedLocale);
        
        $dbMock->expects($this->once())
            ->method('getPrompt')
            ->willReturn($prompt); 

        $dto = $this->factory->createChatPromptMessageDto($dbMock, $this->updateMock);

        $this->assertInstanceOf(ChatPromptMessageDto::class, $dto);
        $this->assertEquals($expectedMessage, $dto->getMessage());
        $this->assertEquals($expectedRole, $dto->getPrompt()); // El role del Prompt es el 'prompt' del DTO
    }
}

