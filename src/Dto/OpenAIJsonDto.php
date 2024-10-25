<?php

namespace App\Dto;

class OpenAIJsonDto
{
    private string $model;
    private array $messages;

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setMessages(array $messages): self
    {
        $this->messages = $messages;
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
