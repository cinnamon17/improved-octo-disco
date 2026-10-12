<?php

namespace App\Dto;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendAIMessageCommandDto
{
    public function __construct(
        private ChatPromptMessageDto $chatDto, 
        private int $chatId,
        private ?int $usageLogId = null,
    ) {}
    
    public function getChatDto(): ChatPromptMessageDto { return $this->chatDto; }
    public function getChatId(): int { return $this->chatId; }

    /**
     * Log de ai_usage_log ya reservado. Lo liquida TelegramMessageProcessor.
     * null cuando el comando se despacha sin cobro (tests, usos internos).
     */
    public function getUsageLogId(): ?int { return $this->usageLogId; }
    public function hasUsageLog(): bool { return $this->usageLogId !== null; }
}
