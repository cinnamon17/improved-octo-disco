<?php

namespace App\Service;

use App\Dto\CallbackQueryDto;
use App\Dto\UpdateDto;

class TelegramBotUpdate
{
    private UpdateDto $update;

    public function __construct(RequestSerializer $update)
    {
        $this->update = $update->create();
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
        return $this->update->getMessage()->getChat()->getId();
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
        return $this->getLanguageCode() ?? $this->getCallbackQueryLanguageCode() ?? 'en';
    }

    public function isCallbackQuery(): bool
    {
        return $this->update->getCallbackQuery() ? true : false;
    }
}
