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
    private AIService $aiServiceMock;
    private TelegramClient $telegramClientMock;
    private TelegramMessageProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocks de las dependencias
        $this->aiServiceMock = $this->createMock(AIService::class);
        $this->telegramClientMock = $this->createMock(TelegramClient::class);

        // Inicializar el procesador con los mocks
        $this->processor = new TelegramMessageProcessor(
            $this->aiServiceMock,
            $this->telegramClientMock
        );
    }

    /**
     * @dataProvider responseLengthProvider
     */
    public function testInvokeHandlesResponseCorrectly(string $fullResponse, int $expectedCallCount): void
    {
        $chatId = 12345;
        $chatDtoStub = $this->createStub(ChatPromptMessageDto::class);

        // Crear el comando
        $command = new SendAIMessageCommandDto($chatDtoStub, $chatId);

        // 1. Simular la respuesta del AIService
        $this->aiServiceMock->expects($this->once())
            ->method('getChatCompletion')
            ->with($chatDtoStub)
            ->willReturn($fullResponse);

        // 2. Verificar la llamada a TelegramClient (lógica de chunking)
        $this->telegramClientMock->expects($this->exactly($expectedCallCount))
            ->method('sendGenericMessage')
            // Utilizamos callback para verificar que cada chunk no exceda el límite de 4096
            ->with($this->callback(function ($message) {
                // Assert de que cada mensaje enviado es <= 4096
                $this->assertLessThanOrEqual(4096, strlen($message));
                return true;
            }), $chatId)
            ->willReturn(['ok' => true]);

        // Ejecutar el método __invoke con el comando
        $this->processor->__invoke($command);
    }

    // --- Data Provider ---

public static function responseLengthProvider(): array
{
    // El límite de caracteres de Telegram para un solo mensaje es 4096.
    $limit = 4096;

    return [
        // [fullResponse, expectedCallCount]
        'Short response (1 call)' => [
            str_repeat('a', 100), // $fullResponse
            1,                     // $expectedCallCount
        ],
        'Exact limit response (1 call)' => [
            str_repeat('b', $limit),
            1,
        ],
        'Slightly over limit (2 calls)' => [
            str_repeat('c', $limit + 1),
            2,
        ],
        'Multiple chunks (3 calls)' => [
            str_repeat('d', ($limit * 2) + 500),
            3,
        ],
        'Large response (10 calls)' => [
            str_repeat('e', $limit * 9 + 1),
            10,
        ],
    ];
}
}
