<?php

namespace App\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
use App\Entity\Message;
use App\Entity\User;

class TelegramDtoFactory
{
    private BotUpdateTranslator $bt;
    private TelegramBotUpdate $update;

    public function __construct(BotUpdateTranslator $bt, TelegramBotUpdate $update)
    {
        $this->bt = $bt;
        $this->update = $update;
    }

    public function createCallbackQueryParams(): array
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message');
        return (new TelegramMessageDto())
            ->setMethod('sendMessage')
            ->setChatId($this->update->getCallbackQueryChatId())
            ->setText($setModeMessage)
            ->toArray();
    }

    public function createSendMessageParams(string $message): array
    {
        return (new TelegramMessageDto())
            ->setChatId($this->update->getChatId())
            ->setMethod('sendMessage')
            ->setText($message)
            ->toArray();
    }

    public function createAdminSendMessageParams(): array
    {
        return (new TelegramMessageDto())
            ->setChatId(1136298813)
            ->setMethod('sendMessage')
            ->setText($this->update->getMessageText())
            ->toArray();
    }

    public function createSendChatActionParams(string $action): array
    {
        return (new TelegramActionDto())
            ->setChatId($this->update->getChatId())
            ->setMethod('sendChatAction')
            ->setAction($action)
            ->toArray();
    }

    public function createAnswerCallbackQueryParams(): array
    {
        return (new AnswerCallbackQueryDto())
            ->setId($this->update->getCallbackQueryId())
            ->setMethod('answerCallbackQuery')
            ->toArray();
    }

    public function createSendInlineKeyboardParams(): array
    {
        $translatorButton = (new InlineKeyboardButtonDto())
            ->setText($this->bt->getTranslatorMessage() . " 🈯")
            ->setData($this->bt->getTranslatorMessage());

        $assistantButton = (new InlineKeyboardButtonDto())
            ->setText($this->bt->getAssistantMessage() . " 👨🏻‍🏫")
            ->setData($this->bt->getAssistantMessage());

        $cheffButton = (new InlineKeyboardButtonDto())
            ->setText('chef 🧑🏻‍🍳')
            ->setData('chef');

        $doctorButton = (new InlineKeyboardButtonDto())
            ->setText('doctor 👨🏻‍⚕️')
            ->setData('doctor');

        $bussinessButton = (new InlineKeyboardButtonDto())
            ->setText($this->bt->getBusinessMessage() . '💡')
            ->setData('startup');

        $buttonRow1 = (new InlineKeyboardButtonRowDto())
            ->add($translatorButton)
            ->add($assistantButton);

        $buttonRow2 = (new InlineKeyboardButtonRowDto())
            ->add($cheffButton)
            ->add($doctorButton);

        $buttonRow3 = (new InlineKeyboardButtonRowDto())
            ->add($bussinessButton);

        $inlineKeyboardDto = (new InlineKeyboardDto())
            ->add($buttonRow1)
            ->add($buttonRow2)
            ->add($buttonRow3);

        return (new TelegramMessageDto())
            ->setMethod('sendMessage')
            ->setChatId($this->update->getChatId())
            ->setText($this->bt->getCharacterMessage())
            ->setReplyMarkup($inlineKeyboardDto)
            ->toArray();
    }

    public function createChatPromptMessageDto(DBService $db): ChatPromptMessageDto
    {
        $prompt = $db->getPrompt($this->createUser(), $this->update->getLocale());
        return (new ChatPromptMessageDto)
            ->setMessage($this->update->getMessageText())
            ->setPrompt($prompt->getRole());
    }

    public function createUserBotMode(): User
    {
        return (new User())
            ->setChatId($this->update->getCallbackQueryChatId())
            ->setMode($this->update->getCallbackQueryData());
    }

    public function createUser(): User
    {
        return (new User())
            ->setChatId($this->update->getChatId())
            ->setIsBot($this->update->getIsBot())
            ->setMode($this->bt->getAssistantMessage())
            ->setFirstName($this->update->getFirstName());
    }

    public function createMessage(): Message
    {
        return (new Message())
            ->setText($this->update->getMessageText())
            ->setMessageId($this->update->getMessageId());
    }

    public function createChatIdFromUpdate(): int
    {
        return $this->update->getChatId() ?? $this->update->getCallbackQuery()->getFrom()->getId();
    }
}
