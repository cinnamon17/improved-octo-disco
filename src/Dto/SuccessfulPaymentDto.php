<?php

namespace App\Dto;

/**
 * Payload de successful_payment dentro de message.
 * Telegram solo lo envia tras confirmar un pre_checkout_query con ok=true.
 */
class SuccessfulPaymentDto
{
    private string $currency;
    private int $total_amount;
    private string $invoice_payload;
    private ?string $subscription_expiration_date = null;
    private bool $is_recurring = false;
    private bool $is_first_recurring = false;
    private ?string $shipping_option_id = null;
    private ?string $order_info = null;
    private string $telegram_payment_charge_id;
    private string $provider_payment_charge_id;

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

    public function setTelegramPaymentChargeId(string $chargeId): self
    {
        $this->telegram_payment_charge_id = $chargeId;
        return $this;
    }

    public function getTelegramPaymentChargeId(): string
    {
        return $this->telegram_payment_charge_id;
    }

    public function setProviderPaymentChargeId(string $chargeId): self
    {
        $this->provider_payment_charge_id = $chargeId;
        return $this;
    }

    public function getProviderPaymentChargeId(): string
    {
        return $this->provider_payment_charge_id;
    }
}