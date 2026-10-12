<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\SendAIMessageCommandDto;
use App\Entity\AiUsageLog;
use App\Service\AiConsumptionService;
use App\Service\AiTokenCounter;
use App\Service\AIService;
use App\Service\TelegramClient;
use App\Service\TelegramMessageProcessor;
use PHPUnit\Framework\TestCase;

class TelegramMessageProcessorTest extends TestCase
{
    private $aiServiceMock;
    private $telegramClientMock;
    private $consumptionMock;
    private $processor;

    protected function setUp(): void
    {
        $this->aiServiceMock = $this->createMock(AIService::class);
        $this->telegramClientMock = $this->createMock(TelegramClient::class);
        $this->consumptionMock = $this->createMock(AiConsumptionService::class);

        // Un comando sin usageLogId significa que no hay nada que liquidar.
        $this->consumptionMock->method('getLog')->willReturn(null);
        $this->consumptionMock->expects($this->never())->method('complete');
        $this->consumptionMock->expects($this->never())->method('refundEmpty');
        $this->consumptionMock->expects($this->never())->method('fail');

        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock,
            $this->consumptionMock,
            new AiTokenCounter()
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

    /**
     * Un stream que falla tras entregar texto cobra proporcional (D2): el
     * usuario ya leyo parte, asi que no se devuelve todo.
     */
    public function testFailureWithDeliveredTokensSettlesCutoff(): void
    {
        $log = $this->createStub(AiUsageLog::class);

        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);
        $command = new SendAIMessageCommandDto($chatDtoStub, 12345, 7);

        $this->consumptionMock = $this->createMock(AiConsumptionService::class);
        $this->consumptionMock->method('getLog')->willReturn($log);
        $this->consumptionMock->expects($this->once())->method('logFailure');
        $this->consumptionMock->expects($this->once())
            ->method('settleCutoff')
            ->with($log, $this->greaterThan(0));
        $this->consumptionMock->expects($this->never())->method('fail');

        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock,
            $this->consumptionMock,
            new AiTokenCounter()
        );

        $generator = function() {
            yield "texto entregado";
            throw new \RuntimeException('se corto el stream');
        };

        $this->aiServiceMock->method('getChatCompletionStream')
            ->willReturn($generator());

        $this->processor->__invoke($command);
    }

    /**
     * Un stream que falla sin entregar nada devuelve la reserva integra.
     */
    public function testFailureWithoutTokensRefundsEverything(): void
    {
        $log = $this->createStub(AiUsageLog::class);

        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);
        $command = new SendAIMessageCommandDto($chatDtoStub, 12345, 7);

        $this->consumptionMock = $this->createMock(AiConsumptionService::class);
        $this->consumptionMock->method('getLog')->willReturn($log);
        $this->consumptionMock->expects($this->once())->method('logFailure');
        $this->consumptionMock->expects($this->once())
            ->method('fail')
            ->with($log, $this->isType('string'));
        $this->consumptionMock->expects($this->never())->method('settleCutoff');

        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock,
            $this->consumptionMock,
            new AiTokenCounter()
        );

        $generator = function() {
            throw new \RuntimeException('deepseek caido');
            yield 'nunca';
        };

        $this->aiServiceMock->method('getChatCompletionStream')
            ->willReturn($generator());

        $this->processor->__invoke($command);
    }

    /**
     * Un stream vacio no es un fallo pero tampoco entrega nada: no se cobra.
     */
    public function testEmptyStreamIsRefunded(): void
    {
        $log = $this->createStub(AiUsageLog::class);

        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);
        $command = new SendAIMessageCommandDto($chatDtoStub, 12345, 7);

        $this->consumptionMock = $this->createMock(AiConsumptionService::class);
        $this->consumptionMock->method('getLog')->willReturn($log);
        $this->consumptionMock->expects($this->once())->method('refundEmpty')->with($log);
        $this->consumptionMock->expects($this->never())->method('complete');

        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock,
            $this->consumptionMock,
            new AiTokenCounter()
        );

        $this->aiServiceMock->method('getChatCompletionStream')
            ->willReturn((function() { yield from []; })());

        $this->processor->__invoke($command);
    }
}