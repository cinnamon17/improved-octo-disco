<?php

namespace App\Dto;

class TelegramActionDto
{

    private string $method;
    private int $chat_id;
    private string $action;

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setChatId(int $chatId): self
    {
        $this->chat_id = $chatId;
        return $this;
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function toArray(): array
    {

        $params = [
            'method' => $this->getMethod(),
            'chat_id' => $this->getChatId(),
            'action' => $this->getAction()
        ];

        return $params;
    }
}
