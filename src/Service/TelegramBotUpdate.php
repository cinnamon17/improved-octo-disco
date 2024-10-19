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
        $update_id = $this->update->getUpdateId();
        return $update_id;
    }

    public function getMessageText(): ?string
    {
        $message = $this->update->getMessage()?->getText();
        return $message;
    }

    public function getMessageId(): int
    {
        $message_id = $this->update->getMessage()?->getMessageId();
        return $message_id;
    }

    public function getChatId(): int
    {
        $chat_id = $this->update->getMessage()?->getChat()?->getId();
        return $chat_id;
    }

    public function getIsBot(): ?bool
    {
        $is_bot = $this->update->getMessage()?->getFrom()?->getIsBot();
        return $is_bot;
    }

    public function getFirstName(): ?string
    {
        $first_name = $this->update->getMessage()?->getFrom()?->getFirstName();
        return $first_name;
    }

    public function getLastName(): ?string
    {
        $first_name = $this->update->getMessage()?->getFrom()?->getLastName();
        return $first_name;
    }

    public function getUsername(): ?string
    {
        $first_name = $this->update->getMessage()?->getFrom()?->getUsername();
        return $first_name;
    }

    public function getCallbackQueryData(): ?string
    {
        $callbackQueryData = $this->update->getCallbackQuery()?->getData();
        return $callbackQueryData;
    }

    public function getLanguageCode(): ?string
    {
        $languageCode = $this->update->getMessage()?->getFrom()?->getLanguageCode();
        return $languageCode;
    }

    public function getCallbackQuery(): ?CallbackQueryDto
    {
        $callbackQuery = $this->update->getCallbackQuery();
        return $callbackQuery;
    }

    public function getCallbackQueryLanguageCode(): ?string
    {
        $languageCode = $this->update->getCallbackQuery()?->getFrom()?->getLanguageCode();
        return $languageCode;
    }

    public function getCallbackQueryId(): string
    {
        $id = $this->update->getCallbackQuery()->getId();
        return $id;
    }

    public function getCallbackQueryChatId(): int
    {
        $id = $this->update->getCallbackQuery()->getFrom()->getId();
        return $id;
    }
}
