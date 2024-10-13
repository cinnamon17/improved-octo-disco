<?php

namespace App\Dto;

class MessageDto
{
    private int $message_id;
    private UserDto $from;
    private int $date;
    private ChatDto $chat;
    private string $text;

    public function setMessageId(int $id): self
    {
        $this->message_id = $id;
        return $this;
    }

    public function getMessageId(): int
    {
        return $this->message_id;
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

    public function setDate(int $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setChat(ChatDto $chatDto): self
    {
        $this->chat = $chatDto;
        return $this;
    }

    public function getChat(): ChatDto
    {
        return $this->chat;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
}
