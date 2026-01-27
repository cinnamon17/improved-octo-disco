<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\SendAIMessageCommandDto;
use App\Service\AIService;
use App\Service\TelegramClient;
use App\Service\TelegramMessageProcessor;
use PHPUnit\Framework\TestCase;

class TelegramMessageProcessorTest extends TestCase
{
    private $aiServiceMock;
    private $telegramClientMock;
    private $processor;

    protected function setUp(): void
    {
        $this->aiServiceMock = $this->createMock(AIService::class);
        $this->telegramClientMock = $this->createMock(TelegramClient::class);
        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock
        );
    }

    public function testInvokeHandlesStreamingCorrectly(): void
    {
        $chatId = 12345;
        $messageId = 999;
        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);
        $command = new SendAIMessageCommandDto($chatDtoStub, $chatId);

        $generator = function() {
            yield "Hola ";
            yield "esto es ";
            yield "un stream.";
        };

        $this->aiServiceMock->method('getChatCompletionStream')
            ->willReturn($generator());

        $this->telegramClientMock->expects($this->once())
            ->method('sendGenericMessage')
            ->with("Hola ", $chatId)
            ->willReturn(['result' => ['message_id' => $messageId]]);

        $this->telegramClientMock->expects($this->atLeastOnce())
            ->method('editMessageText')
            ->with("Hola esto es un stream.", $chatId, $messageId)
            ->willReturn([]);

        $this->processor->__invoke($command);
    }

    /**
     * Test para verificar el comportamiento cuando supera los 4096 caracteres
     */
    public function testInvokeHandlesChunksWhenLimitExceeded(): void
    {
        $chatId = 12345;
        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);
        $command = new SendAIMessageCommandDto($chatDtoStub, $chatId);

        $generator = function() {
            yield str_repeat('a', 4096);
            yield "b";
        };

        $this->aiServiceMock->method('getChatCompletionStream')
            ->willReturn($generator());

        $this->telegramClientMock->expects($this->atLeastOnce())
            ->method('sendGenericMessage')
            ->willReturn(['result' => ['message_id' => 1]]);

        $this->processor->__invoke($command);
    }
}
