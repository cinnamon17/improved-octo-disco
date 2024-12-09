<?php

namespace App\Service;

use App\Dto\TelegramMessageDto;
use App\Service\DBService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class TelegramService implements LoggerAwareInterface
{
    private HttpService $http;
    private BotUpdateTranslator $bt;
    private DBService $db;
    private LoggerInterface $logger;

    public function __construct(HttpService $http, BotUpdateTranslator $bt, DBService $db)
    {
        $this->http = $http;
        $this->bt = $bt;
        $this->db = $db;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function telegramRequest(array $params): array
    {
        $response = $this->http->request($params);
        return $response;
    }

    public function handleCallbackQuery(): array
    {
        $params = $this->callbackQueryParams();
        $this->setBotMode();
        $this->telegramRequest($params);

        $response =  $this->answerCallbackQuery();
        return $response;
    }

    public function sendMessage(string $message): array
    {
        $params = $this->sendMessageParams($message);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->SendChatActionParams($action);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->answerCallbackQueryParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendInlineKeyboard(): array
    {
        $params = $this->sendInlineKeyboardParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendWelcomeMessage(): array
    {
        $welcomeMessage = $this->bt->translate('welcome.message');
        $response = $this->sendMessage($welcomeMessage);
        return $response;
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

    public function chatCompletion($message): array
    {
        $this->sendChatAction('typing');
        $prompt = $this->db->getPrompt($this->bt);
        return $this->http->chatCompletion($message, $prompt->getRole());
    }

    public function setBotMode(): void
    {
        $this->db->setBotMode($this->bt);
    }

    public function translate(string $message): string
    {
        return $this->bt->translate($message);
    }

    public function log($message, $context = [])
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
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

    private function callbackQueryParams(): array
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message');
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setMethod('sendMessage')
            ->setChatId($this->getCallbackQueryChatId())
            ->setText($setModeMessage);

        return $telegramMessageDto->toArray();
    }

    private function sendMessageParams(string $message): array
    {
        $params = [
            'chat_id' => $this->getChatId(),
            'method' => 'sendMessage',
            'text' => $message
        ];

        return $params;
    }

    private function sendChatActionParams(string $action)
    {
        $params = [
            'chat_id' => $this->getChatId(),
            'method' => 'sendChatAction',
            'action' => $action
        ];

        return $params;
    }
    private function answerCallbackQueryParams(): array
    {
        $id = $this->getCallbackQueryId();
        $params = [
            'callback_query_id' => $id,
            'method' => 'answerCallbackQuery'
        ];

        return $params;
    }

    private function sendInlineKeyboardParams(): array
    {
        $params = [
            'method' => 'sendMessage',
            'chat_id' => $this->getChatId(),
            'text' => $this->bt->getCharacterMessage(),
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => $this->bt->getTranslatorMessage() . " 🈯", 'callback_data' => $this->bt->getTranslatorMessage()],
                        ['text' => $this->bt->getAssistantMessage() . " 👨🏻‍🏫", 'callback_data' => $this->bt->getAssistantMessage()],
                    ],
                    [
                        ['text' => 'chef 🧑🏻‍🍳', 'callback_data' => 'chef'],
                        ['text' => 'doctor 👨🏻‍⚕️', 'callback_data' => 'doctor'],
                    ],
                    [
                        ['text' => $this->bt->getbussinessMessage() . "💡", 'callback_data' => 'startup'],
                    ]
                ]
            ]
        ];

        return $params;
    }
}
