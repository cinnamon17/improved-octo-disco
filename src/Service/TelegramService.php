<?php

namespace App\Service;

use App\Dto\ChatPromptMessageDto;
use App\Service\DBService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class TelegramService implements LoggerAwareInterface
{
    private HttpService $http;
    private BotUpdateTranslator $bt;
    private DBService $db;
    private LoggerInterface $logger;
    private TelegramDtoFactory $dtoFactory;

    public function __construct(HttpService $http, BotUpdateTranslator $bt, DBService $db, TelegramDtoFactory $telegramDtoFactory)
    {
        $this->http = $http;
        $this->bt = $bt;
        $this->db = $db;
        $this->dtoFactory = $telegramDtoFactory;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setBotMode(): void
    {
        $this->db->setBotMode($this->bt);
    }

    public function getChatId(): ?float
    {
        return $this->bt->update()->getChatId();
    }

    public function getFirstName(): ?string
    {
        return $this->bt->update()->getFirstName();
    }

    public function getLastname(): ?string
    {
        return $this->bt->update()->getLastName();
    }

    public function getUsername(): ?string
    {
        return $this->bt->update()->getUsername();
    }

    public function getMessageId()
    {
        return $this->bt->update()->getMessageId();
    }

    public function getMessageText(): ?string
    {
        return $this->bt->update()->getMessageText();
    }

    public function getCallbackQuery()
    {
        return $this->bt->update()->getCallbackQuery();
    }

    public function getCallbackQueryId()
    {
        return $this->bt->update()->getCallbackQueryId();
    }

    public function getCallbackQueryChatId()
    {
        return $this->bt->update()->getCallbackQueryChatId();
    }

    public function getCallbackQueryData()
    {
        return $this->bt->update()->getCallbackQueryData();
    }

    public function getLanguageCode()
    {
        return $this->bt->update()->getLanguageCode();
    }

    public function getIsBot(): bool
    {
        return $this->bt->update()->getIsBot();
    }

    public function insertUserInDb(): void
    {
        $this->db->insertUserInDb($this->bt);
    }

    public function updateUserInDb(): void
    {
        $this->db->updateUserInDb($this->bt);
    }

    public function isUserExists(): bool
    {
        return $this->db->isUserExists($this->bt);
    }

    public function isCallbackQuery(): bool
    {
        return $this->getCallbackQuery() ? true : false;
    }

    public function log($message, $context = [])
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
    }

    public function translate(string $message): string
    {
        return $this->bt->translate($message);
    }

    public function telegramRequest(array $params): array
    {
        return $this->http->request($params);
    }

    public function sendMessage(string $message): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message);
        return $this->telegramRequest($params);
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->dtoFactory->createSendChatActionParams($action);
        return $this->telegramRequest($params);
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->dtoFactory->createAnswerCallbackQueryParams();
        return $this->telegramRequest($params);
    }

    public function sendInlineKeyboard(): array
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams();
        return $this->telegramRequest($params);
    }

    public function sendWelcomeMessage(): array
    {
        $welcomeMessage = $this->bt->translate('welcome.message');
        return $this->sendMessage($welcomeMessage);
    }

    public function chatCompletion(): array
    {
        $this->sendChatAction('typing');
        $chatPromptMessageDto = $this->dtoFactory->createChatPromptMessageDto($this->db);
        return $this->http->chatCompletion($chatPromptMessageDto);
    }

    public function handleCallbackQuery(): array
    {
        $params = $this->dtoFactory->createCallbackQueryParams();
        $this->setBotMode();
        $this->telegramRequest($params);

        $response =  $this->answerCallbackQuery();
        return $response;
    }
}
