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

        $response = $this->aiService->getChatCompletion($command->getChatDto());

        if (strlen($response) < 4096) {
            $this->telegramClient->sendGenericMessage($response, $command->getChatId());
        } else {
            $chunks = str_split($response, 4096);
            foreach ($chunks as $text) {
                $this->telegramClient->sendGenericMessage($text, $command->getChatId());
            }
        }

    }
}
