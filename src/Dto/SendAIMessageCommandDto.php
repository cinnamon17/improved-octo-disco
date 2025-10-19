<?php

namespace App\Dto;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendAIMessageCommandDto
{
    public function __construct(
        private ChatPromptMessageDto $chatDto, 
        private int $chatId 
    ) {}
    
    public function getChatDto(): ChatPromptMessageDto { return $this->chatDto; }
    public function getChatId(): int { return $this->chatId; }
}
