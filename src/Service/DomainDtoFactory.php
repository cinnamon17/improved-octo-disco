<?php

namespace App\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\TelegramBotUpdate;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\PromptRepository;

class DomainDtoFactory
{
    private PromptRepository $promptRepository;
    private BotUpdateTranslator $bt;

    public function __construct(PromptRepository $promptRepository, BotUpdateTranslator $bt)
    {
        $this->promptRepository = $promptRepository;
        $this->bt = $bt;
    }

    public function createChatPromptMessageDto(TelegramBotUpdate $update, string $userMode): ChatPromptMessageDto
    {
        $prompt = $this->promptRepository->findPromptByRoleAndLanguage(
            $userMode, 
            $update->getLocale()
        );

        $systemPrompt = $prompt ? $prompt->getRole() : 'You are a helpful assistant.';

        return (new ChatPromptMessageDto)
            ->setMessage($update->getMessageText())
            ->setPrompt($systemPrompt);
    }

    public function createUser(TelegramBotUpdate $update): User
    {
        return (new User())
            ->setChatId($update->getChatId())
            ->setIsBot($update->getIsBot())
            ->setMode($this->bt->getAssistantMessage($update->getLocale())) 
            ->setFirstName($update->getFirstName());
    }

    public function createUserBotMode(TelegramBotUpdate $update): User
    {
        return (new User())
            ->setChatId($update->getCallbackQueryChatId())
            ->setMode($update->getCallbackQueryData());
    }

    public function createMessage(TelegramBotUpdate $update): Message
    {
        return (new Message())
            ->setText($update->getMessageText())
            ->setMessageId($update->getMessageId());
    }
}

