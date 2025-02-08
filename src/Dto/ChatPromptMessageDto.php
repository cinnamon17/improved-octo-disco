<?php

namespace App\Dto;

class ChatPromptMessageDto
{
    private $message;
    private $prompt;

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function setPrompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
    public function getPrompt(): string
    {
        return $this->prompt;
    }
}
