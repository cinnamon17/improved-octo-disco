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
use App\Dto\TelegramBotUpdate;
use App\Service\TelegramApiDtoFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TelegramDtoFactoryTest extends TestCase
{
    private BotUpdateTranslator $btMock;
    private ContainerBagInterface $envMock;
    private TelegramApiDtoFactory$factory;
    private TelegramBotUpdate $updateMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->btMock = $this->createMock(BotUpdateTranslator::class);
        $this->envMock = $this->createMock(ContainerBagInterface::class);

        $this->factory = new TelegramApiDtoFactory($this->btMock, $this->envMock);

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
        $this->updateMock->method('getLocale')->willReturn('es');
        $this->btMock->method('translate')
            ->with('callbackQuery.message', 'es')
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
}

