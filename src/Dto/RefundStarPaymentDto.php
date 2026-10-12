<?php

namespace App\Dto;

/**
 * refundStarPayment de Telegram. Solo aplica a Stars (XTR): devuelve las
 * Stars al usuario y el pago queda como refunded.
 */
class RefundStarPaymentDto implements TelegramDtoInterface
{
    private string $method = 'refundStarPayment';
    private int $user_id;
    private string $telegram_payment_charge_id;

    public function setUserId(int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }

    public function setTelegramPaymentChargeId(string $chargeId): self
    {
        $this->telegram_payment_charge_id = $chargeId;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'telegram_payment_charge_id' => $this->telegram_payment_charge_id,
        ];
    }
}