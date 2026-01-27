<?php

namespace App\Dto;

class OpenAIJsonDto
{
    private string $model;
    private array $messages;
    private bool $stream = false;

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setStream(bool $stream): self
    {
        $this->stream = $stream;
        return $this;
    }

    public function getStream(): bool
    {
        return $this->stream;
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

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'messages' => array_map(fn($message) => $message->toArray(), $this->messages),
            'stream' => $this->stream
        ];
    }
}
