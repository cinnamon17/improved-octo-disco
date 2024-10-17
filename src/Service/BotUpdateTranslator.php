<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslator
{

    private TelegramBotUpdate $update;
    private TranslatorInterface $translator;

    public function __construct(TelegramBotUpdate $update, TranslatorInterface $translator)
    {
        $this->update = $update;
        $this->translator = $translator;
    }

    public function translate(string $id): string
    {

        $message = $this->translator->trans($id, locale: $this->getLocale());
        return $message;
    }

    public function getAssistantMessage(): string
    {
        return $this->translate('assistant.message');
    }

    public function getCharacterMessage(): string
    {
        return $this->translate('character.message');
    }

    public function getbussinessMessage(): string
    {
        return $this->translate('business.message');
    }

    public function getTranslatorMessage(): string
    {
        return $this->translate('translator.message');
    }

    public function update(): TelegramBotUpdate
    {

        return $this->update;
    }

    public function getLanguagueCode(): ?string
    {
        return $this->update->getLanguageCode();
    }

    public function getCallbackQueryLanguageCode(): ?string
    {
        return $this->update->getCallbackQueryLanguageCode();
    }

    public function getCallbackQueryChatId(): ?string
    {
        return $this->update->getCallbackQueryChatId();
    }

    public function getLocale(): ?string
    {
        return $this->getLanguagueCode() ?? $this->getCallbackQueryLanguageCode();
    }
}
