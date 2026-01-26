<?php

namespace App\Service;

use App\Dto\SendAIMessageCommandDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramMessageProcessor
{
    private AIService $aiService;
    private TelegramClient $telegramClient;

    public function __construct(AIService $aiService, TelegramClient $telegramClient)
    {
        $this->aiService = $aiService;
        $this->telegramClient = $telegramClient;
    }

    public function __invoke(SendAIMessageCommandDto $command): void
    {
        $chunks = $this->aiService->getChatCompletionStream($command->getChatDto());

        $currentText = "";
        $messageId = null;
        $lastUpdateTime = microtime(true);
        $limit = 4096;

        foreach ($chunks as $chunk) {
            $currentText .= $chunk;

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

    }
}
