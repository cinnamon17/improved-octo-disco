<?php

namespace App\Service;

use App\Dto\SendAIMessageCommandDto;
use App\Entity\AiUsageLog;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramMessageProcessor
{
    private AIService $aiService;
    private TelegramClient $telegramClient;
    private AiConsumptionService $consumptionService;
    private AiTokenCounter $tokenCounter;

    public function __construct(
        AIService $aiService,
        TelegramClient $telegramClient,
        AiConsumptionService $consumptionService,
        AiTokenCounter $tokenCounter
    ) {
        $this->aiService = $aiService;
        $this->telegramClient = $telegramClient;
        $this->consumptionService = $consumptionService;
        $this->tokenCounter = $tokenCounter;
    }

    public function __invoke(SendAIMessageCommandDto $command): void
    {
        $log = $this->resolveLog($command);

        $receivedTokens = 0;

        try {
            $chunks = $this->aiService->getChatCompletionStream($command->getChatDto());

            $currentText = "";
            $messageId = null;
            $lastUpdateTime = microtime(true);
            $limit = 4096;

            foreach ($chunks as $chunk) {
                $currentText .= $chunk;
                $receivedTokens += $this->tokenCounter->count($chunk);

                if (mb_strlen($currentText) >= $limit) {
                    if ($messageId) {
                        $this->telegramClient->editMessageText($currentText, $command->getChatId(), $messageId);
                    }

                    $currentText = "";
                    $messageId = null;
                    continue;
                }

                if ($messageId === null && mb_strlen($currentText) > 0) {
                    $response = $this->telegramClient->sendGenericMessage($currentText, $command->getChatId());
                    $messageId = $response['result']['message_id'] ?? null;
                    $lastUpdateTime = microtime(true);
                    continue;
                }

                if ($messageId !== null && (microtime(true) - $lastUpdateTime > 1.5)) {
                    $this->telegramClient->editMessageText($currentText, $command->getChatId(), $messageId);
                    $lastUpdateTime = microtime(true);
                }
            }

            if ($messageId !== null && mb_strlen($currentText) > 0) {
                $this->telegramClient->editMessageText($currentText, $command->getChatId(), $messageId);
            }
        } catch (\Throwable $e) {
            // La reserva ya se hizo en el dispatcher. Si llego a entrar texto,
            // el usuario ya lo leyo y se cobra proporcional (D2). Si no llego
            // nada, devolucion integra.
            $this->consumptionService->logFailure($e);

            if ($log === null) {
                return;
            }

            if ($receivedTokens === 0) {
                $this->consumptionService->fail($log, $e->getMessage());
            } else {
                $this->consumptionService->settleCutoff($log, $receivedTokens);
            }

            // No se relanza: Messenger reintentaria el mensaje y el cobro ya
            // esta liquidado, con lo que el reintento cobraria dos veces.
            return;
        }

        $this->settle($log, $receivedTokens);
    }

    /**
     * Sin contenido el usuario no recibio nada, asi que no se cobra.
     * Un stream cortado con contenido se cobra proporcional.
     */
    private function settle(?AiUsageLog $log, int $receivedTokens): void
    {
        // Comando despachado sin reserva: nada que liquidar.
        if ($log === null) {
            return;
        }

        if ($receivedTokens === 0) {
            $this->consumptionService->refundEmpty($log);

            return;
        }

        $this->consumptionService->complete($log, $receivedTokens);
    }

    private function resolveLog(SendAIMessageCommandDto $command): ?AiUsageLog
    {
        if (!$command->hasUsageLog()) {
            return null;
        }

        $log = $this->consumptionService->getLog($command->getUsageLogId());

        if ($log === null) {
            // El log se borro (o la reserva ya no existe). Sin el no hay nada
            // que liquidar, pero el usuario ya recibio su respuesta.
            $this->consumptionService->logFailure(
                new \RuntimeException(sprintf('No existe el log de uso %d', $command->getUsageLogId()))
            );
        }

        return $log;
    }
}