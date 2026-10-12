<?php

namespace App\Dto;

/**
 * Factura de Telegram Stars. currency siempre XTR y provider_token vacio:
 * Stars no pasan por proveedor externo, los liquida Telegram.
 */
class SendInvoiceDto implements TelegramDtoInterface
{
    private string $method = 'sendInvoice';
    private int $chat_id;
    private string $title;
    private string $description;
    private string $payload;
    private string $currency = 'XTR';
    private array $prices = [];

    public function setChatId(int $chatId): self
    {
        $this->chat_id = $chatId;
        return $this;
    }

    public function getChatId(): int
    {
        return $this->chat_id;
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

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setPayload(string $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function addPrice(string $label, int $amount): self
    {
        $this->prices[] = ['label' => $label, 'amount' => $amount];
        return $this;
    }

    public function toArray(): array
    {
        return [
            'chat_id' => $this->getChatId(),
            'title' => $this->title,
            'description' => $this->description,
            'payload' => $this->payload,
            'currency' => $this->currency,
            'provider_token' => '',
            'prices' => $this->prices,
        ];
    }
}