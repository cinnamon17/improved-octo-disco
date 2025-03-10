<?php

namespace App\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;

class TelegramDtoFactory
{
    private BotUpdateTranslator $bt;

    public function __construct(BotUpdateTranslator $bt)
    {
        $this->bt = $bt;
    }

    public function createCallbackQueryParams(): array
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message');
        return (new TelegramMessageDto())
            ->setMethod('sendMessage')
            ->setChatId($this->bt->update()->getCallbackQueryChatId())
            ->setText($setModeMessage)
            ->toArray();
    }

    public function createSendMessageParams(string $message): array
    {
        return (new TelegramMessageDto())
            ->setChatId($this->bt->update()->getChatId())
            ->setMethod('sendMessage')
            ->setText($message)
            ->toArray();
    }

    public function createSendChatActionParams(string $action): array
    {
        return (new TelegramActionDto())
            ->setChatId($this->bt->update()->getChatId())
            ->setMethod('sendChatAction')
            ->setAction($action)
            ->toArray();
    }

    public function createAnswerCallbackQueryParams(): array
    {
        return (new AnswerCallbackQueryDto())
            ->setId($this->bt->update()->getCallbackQueryId())
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
            ->setText($this->bt->getbussinessMessage() . '💡')
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
            ->setChatId($this->bt->update()->getChatId())
            ->setText($this->bt->getCharacterMessage())
            ->setReplyMarkup($inlineKeyboardDto)
            ->toArray();
    }
}
