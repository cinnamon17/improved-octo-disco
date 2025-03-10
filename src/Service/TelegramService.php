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
        $bool = $this->db->isUserExists($this->bt);
        return $bool;
    }

    public function isCallbackQuery(): bool
    {
        $data = $this->getCallbackQuery();
        return $data ? true : false;
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
        $response = $this->http->request($params);
        return $response;
    }


    public function sendMessage(string $message): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->dtoFactory->createSendChatActionParams($action);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->dtoFactory->createAnswerCallbackQueryParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendInlineKeyboard(): array
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendWelcomeMessage(): array
    {
        $welcomeMessage = $this->bt->translate('welcome.message');
        $response = $this->sendMessage($welcomeMessage);
        return $response;
    }


    public function chatCompletion($message): array
    {
        $this->sendChatAction('typing');
        $prompt = $this->db->getPrompt($this->bt);
        $chatPromptMessageDto = (new ChatPromptMessageDto())
            ->setMessage($message)
            ->setPrompt($prompt->getRole());
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
