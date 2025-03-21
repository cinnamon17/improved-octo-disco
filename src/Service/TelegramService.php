<?php

namespace App\Service;

use App\Service\DBService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class TelegramService implements LoggerAwareInterface
{
    private HttpService $http;
    private DBService $db;
    private LoggerInterface $logger;
    private TelegramDtoFactory $dtoFactory;
    private BotUpdateTranslator $bt;

    public function __construct(
        HttpService $http,
        DBService $db,
        TelegramDtoFactory $telegramDtoFactory,
        BotUpdateTranslator $botUpdateTranslator
    ) {
        $this->http = $http;
        $this->db = $db;
        $this->dtoFactory = $telegramDtoFactory;
        $this->bt = $botUpdateTranslator;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function log($message, $context = [])
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
    }

    public function telegramRequest(array $params): array
    {
        return $this->http->request($params);
    }

    public function sendMessage(string $message): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message);
        $adminParams = $this->dtoFactory->createAdminSendMessageParams();
        $this->telegramRequest($adminParams);
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
        $welcomeMessage = $this->bt->getWelcomeMessage();
        return $this->sendMessage($welcomeMessage);
    }

    public function chatCompletion(): array
    {
        $this->sendChatAction('typing');
        $chatPromptMessageDto = $this->dtoFactory->createChatPromptMessageDto($this->db);
        return $this->http->chatCompletion($chatPromptMessageDto);
    }

    public function setBotMode(): void
    {
        $user = $this->dtoFactory->createUserBotMode();
        $this->db->updateUserMode($user);
    }

    public function insertUserInDb(): void
    {
        $user = $this->dtoFactory->createUser();
        $this->db->insertUserInDb($user);
    }

    public function updateUserInDb(): void
    {
        $user = $this->dtoFactory->createUser();
        $message = $this->dtoFactory->createMessage();
        $this->db->updateUserInDb($user, $message);
    }

    public function isUserExists(): bool
    {
        $chatId = $this->dtoFactory->createChatIdFromUpdate();
        return $this->db->isUserExists($chatId);
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
