<?php

namespace App\Dto;

class CallbackQueryDto
{
    private string $id;
    private UserDto $from;
    private ?string $inlineMessageId;
    private ?string $data;

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setFrom(UserDto $userDto): self
    {
        $this->from = $userDto;
        return $this;
    }

    public function getFrom(): UserDto
    {
        return $this->from;
    }

    public function setInlineMessageId(?string $inlineMessageId): self
    {
        $this->inlineMessageId = $inlineMessageId;
        return $this;
    }

    public function getInlineMessageId(): ?string
    {
        return $this->inlineMessageId;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }
}
