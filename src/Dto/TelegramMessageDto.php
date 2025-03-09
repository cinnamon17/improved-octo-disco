<?php

namespace App\Dto;

class TelegramMessageDto
{

    private string $method;
    private int $chat_id;
    private string $text;
    private ?InlineKeyboardDto $reply_markup = null;

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

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setReplyMarkup(InlineKeyboardDto $inlineKeyboard): self
    {

        $this->reply_markup = $inlineKeyboard;
        return $this;
    }

    public function getReplyMarkup(): array | string
    {
        return $this->reply_markup?->getKeyboard() ?? '';
    }

    public function toArray(): array
    {

        $params = [
            'method' => $this->getMethod(),
            'chat_id' => $this->getChatId(),
            'text' => $this->getText(),
            'reply_markup' => $this->getReplyMarkup()
        ];

        return $params;
    }
}
