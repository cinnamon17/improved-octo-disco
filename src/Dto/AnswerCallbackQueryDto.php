<?php

namespace App\Dto;

class AnswerCallbackQueryDto
{
    private string $id;
    private string $method;

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function toArray(): array
    {

        $params = [
            'callback_query_id' => $this->getId(),
            'method' => $this->getMethod(),
        ];

        return $params;
    }
}
