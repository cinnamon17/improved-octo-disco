<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslator
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
}
