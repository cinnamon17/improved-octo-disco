<?php

namespace App\Dto;

class TelegramBotUpdate
{
    private UpdateDto $update;

    public function __construct(UpdateDto $update)
    {
	$this->update = $update;
    }

    public function getUpdateId(): int
    {
        return $this->update->getUpdateId();
    }

    public function getMessageText(): ?string
    {
        return $this->update->getMessage()?->getText();
    }

    public function getMessageId(): int
    {
        return $this->update->getMessage()?->getMessageId();
    }

    public function getChatId(): ?int
    {
        return $this->update->getMessage()?->getChat()?->getId();
    }

    public function getIsBot(): ?bool
    {
        return $this->update->getMessage()?->getFrom()?->getIsBot();
    }

    public function getFirstName(): ?string
    {
        return $this->update->getMessage()?->getFrom()?->getFirstName();
    }

    public function getLastName(): ?string
    {
        return $this->update->getMessage()?->getFrom()?->getLastName();
    }

    public function getUsername(): ?string
    {
        return $this->update->getMessage()?->getFrom()?->getUsername();
    }

    public function getCallbackQueryData(): ?string
    {
        return $this->update->getCallbackQuery()?->getData();
    }

    private function getLanguageCode(): ?string
    {
        return $this->update->getMessage()?->getFrom()?->getLanguageCode();
    }

    public function getCallbackQuery(): ?CallbackQueryDto
    {
        return $this->update->getCallbackQuery();
    }

    public function getCallbackQueryLanguageCode(): ?string
    {
        return $this->update->getCallbackQuery()?->getFrom()?->getLanguageCode();
    }

    public function getCallbackQueryId(): string
    {
        return $this->update->getCallbackQuery()->getId();
    }

    public function getCallbackQueryChatId(): int
    {
        return $this->update->getCallbackQuery()->getFrom()->getId();
    }

    public function getLocale(): ?string
    {
	return $this->getLanguageCode()
	    ?? $this->getCallbackQueryLanguageCode()
	    ?? $this->getPreCheckoutQueryLanguageCode()
	    ?? 'en';
    }

    public function isCallbackQuery(): bool
    {
        return $this->update->getCallbackQuery() ? true : false;
    }

    public function isPreCheckoutQuery(): bool
    {
        return $this->update->getPreCheckoutQuery() !== null;
    }

    public function getPreCheckoutQuery(): ?PreCheckoutQueryDto
    {
        return $this->update->getPreCheckoutQuery();
    }

    public function hasSuccessfulPayment(): bool
    {
        return $this->update->getMessage()?->getSuccessfulPayment() !== null;
    }

    public function getSuccessfulPayment(): ?SuccessfulPaymentDto
    {
        return $this->update->getMessage()?->getSuccessfulPayment();
    }

    public function hasRefundedPayment(): bool
    {
        return $this->update->getMessage()?->getRefundedPayment() !== null;
    }

    public function getRefundedPayment(): ?RefundedPaymentDto
    {
        return $this->update->getMessage()?->getRefundedPayment();
    }

    /**
     * Subconjunto del update para trazarabilidad. Solo datos de pago: el texto
     * del usuario ya vive en la tabla message.
     */
    public function toRawArray(): array
    {
        return [
            'update_id' => $this->getUpdateId(),
            'successful_payment' => $this->hasSuccessfulPayment()
                ? $this->getSuccessfulPayment()
                : null,
        ];
    }

    private function getPreCheckoutQueryLanguageCode(): ?string
    {
        return $this->update->getPreCheckoutQuery()?->getFrom()->getLanguageCode();
    }
}
