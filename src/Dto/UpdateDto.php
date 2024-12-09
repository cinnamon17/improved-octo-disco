<?php

namespace App\Dto;

class UpdateDto
{
    private int $update_id;
    private ?MessageDto $message = null;
    private ?CallbackQueryDto $callback_query = null;

    public function setUpdateId(int $update_id): self
    {
        $this->update_id = $update_id;
        return $this;
    }

    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    public function setMessage(?MessageDto $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): ?MessageDto
    {
        return $this->message;
    }

    public function setCallbackQuery(?CallbackQueryDto $callbackQueryDto): self
    {
        $this->callback_query = $callbackQueryDto;
        return $this;
    }

    public function getCallbackQuery(): ?CallbackQueryDto
    {
        return $this->callback_query;
    }
}
