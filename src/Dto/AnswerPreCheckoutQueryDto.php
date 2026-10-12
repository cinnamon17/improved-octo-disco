<?php

namespace App\Dto;

/**
 * Respuesta a pre_checkout_query. Telegram da 10 segundos: si no llega, el pago falla.
 */
class AnswerPreCheckoutQueryDto implements TelegramDtoInterface
{
    private string $method = 'answerPreCheckoutQuery';
    private string $pre_checkout_query_id;
    private bool $ok;
    private ?string $error_message = null;

    public function setPreCheckoutQueryId(string $id): self
    {
        $this->pre_checkout_query_id = $id;
        return $this;
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

    public function setOk(bool $ok): self
    {
        $this->ok = $ok;
        return $this;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->error_message = $errorMessage;
        return $this;
    }

    public function toArray(): array
    {
        $params = [
            'pre_checkout_query_id' => $this->pre_checkout_query_id,
            'ok' => $this->ok,
        ];

        if (!$this->ok && $this->error_message !== null) {
            $params['error_message'] = $this->error_message;
        }

        return $params;
    }
}