<?php

namespace App\Service;

use App\Service\HttpService;
use App\Service\TelegramDtoFactory;

class TelegramClient
{
    private HttpService $http;
    private TelegramDtoFactory $dtoFactory;

    public function __construct(HttpService $http, TelegramDtoFactory $telegramDtoFactory)
    {
        $this->http = $http;
        $this->dtoFactory = $telegramDtoFactory;
    }

    public function sendMessage(string $message, TelegramBotUpdate $update): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message, $update);
        return $this->http->telegramRequest($params);
    }

    public function sendAdminMessageFromUpdate(TelegramBotUpdate $update): array
    {
        $adminParams = $this->dtoFactory->createAdminSendMessageParams($update);
        return $this->http->telegramRequest($adminParams);
    }

    public function sendChatAction(string $action, TelegramBotUpdate $update): array
    {
        $params = $this->dtoFactory->createSendChatActionParams($action, $update);
        return $this->http->telegramRequest($params);
    }

    public function answerCallbackQuery(TelegramBotUpdate $update): array
    {
        $params = $this->dtoFactory->createAnswerCallbackQueryParams($update);
        return $this->http->telegramRequest($params);
    }

    public function sendInlineKeyboard(TelegramBotUpdate $update): array
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams($update);
        return $this->http->telegramRequest($params);
    }
}

