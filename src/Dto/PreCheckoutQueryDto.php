<?php

namespace App\Dto;

/**
 * Update pre_checkout_query de Telegram.
 * Llega como campo de primer nivel del update, no dentro de message.
 */
class PreCheckoutQueryDto
{
    private string $id;
    private UserDto $from;
    private string $currency;
    private int $total_amount;
    private string $invoice_payload;

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setFrom(UserDto $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function getFrom(): UserDto
    {
        return $this->from;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setTotalAmount(int $totalAmount): self
    {
        $this->total_amount = $totalAmount;
        return $this;
    }

    public function getTotalAmount(): int
    {
        return $this->total_amount;
    }

    public function setInvoicePayload(string $invoicePayload): self
    {
        $this->invoice_payload = $invoicePayload;
        return $this;
    }

    public function getInvoicePayload(): string
    {
        return $this->invoice_payload;
    }
}