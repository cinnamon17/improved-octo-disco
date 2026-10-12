<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslator
{

    private TranslatorInterface $translator;
    private array $topUpPacks;
    private int $starsToCredits;

    public function __construct(
        TranslatorInterface $translator,
        array $topUpPacks = [10, 25, 50],
        int $starsToCredits = 1,
    ) {
        $this->translator = $translator;
        $this->topUpPacks = $topUpPacks;
        $this->starsToCredits = $starsToCredits;
    }

    /**
     * Packs de recarga disponibles, en Stars. Configurables por entorno.
     *
     * @return int[]
     */
    public function getTopUpPacks(): array
    {
        return array_map('intval', $this->topUpPacks);
    }

    public function translate(string $id, string $locale): string
    {
        return $this->translator->trans($id, locale: $locale);
    }

    public function getAssistantMessage(string $locale): string
    {
        return $this->translate('assistant.message', $locale);
    }

    public function getCharacterMessage(string $locale): string
    {
        return $this->translate('character.message', $locale);
    }

    public function getBusinessMessage(string $locale): string
    {
        return $this->translate('business.message', $locale);
    }

    public function getTranslatorMessage(string $locale): string
    {
        return $this->translate('translator.message', $locale);
    }

    public function getWelcomeMessage(string $locale): string
    {
        return $this->translate('welcome.message', $locale);
    }

    public function getBalanceMessage(string $locale, int $credits): string
    {
	return $this->translator->trans('balance.message', [
	    '%credits%' => $credits,
	], 'messages', $locale);
    }

    public function getInsufficientCreditsMessage(string $locale, int $credits, int $price): string
    {
	return $this->translator->trans('credits.insufficient', [
	    '%credits%' => $credits,
	    '%price%' => $price,
	], 'messages', $locale);
    }

    public function getTopUpMessage(string $locale): string
    {
        return $this->translate('topup.message', $locale);
    }

    public function getInvoiceTitle(string $locale): string
    {
        return $this->translate('invoice.title', $locale);
    }

    public function getInvoiceLabel(string $locale, int $stars): string
    {
	return $this->translator->trans('invoice.label', [
	    '%stars%' => $stars,
	], 'messages', $locale);
    }

    public function getPaymentInvalidMessage(string $locale): string
    {
        return $this->translate('payment.invalid', $locale);
    }

    public function getPaymentConfirmedMessage(string $locale, int $stars): string
    {
	return $this->translator->trans('payment.confirmed', [
	    '%stars%' => $stars,
	], 'messages', $locale);
    }

    public function getPaymentRefundedMessage(string $locale): string
    {
        return $this->translate('payment.refunded', $locale);
    }

    public function getPackButtonText(string $locale, int $stars): string
    {
	return $this->translator->trans('topup.pack_button', [
	    '%stars%' => $stars,
	    '%credits%' => $stars * $this->starsToCredits,
	], 'messages', $locale);
    }
}
