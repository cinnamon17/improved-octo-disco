<?php

namespace App\Service;

use App\Dto\TelegramBotUpdate;
use App\Dto\TelegramDtoInterface;

class TelegramClient
{
    private TelegramHttpService $telegramHttpService;
    private TelegramApiDtoFactory $apiDtoFactory;

    public function __construct(TelegramHttpService $telegramHttpService, TelegramApiDtoFactory $telegramApiDtoFactory)
    {
	$this->telegramHttpService = $telegramHttpService;
	$this->apiDtoFactory = $telegramApiDtoFactory;
    }

    public function sendDto(TelegramDtoInterface $dto): array
    {
	return $this->telegramHttpService->telegramRequest($dto);
    }

    public function sendMessage(string $message, TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createSendMessageParams($message, $update);
	return $this->sendDto($params);
    }

    public function sendAdminMessageFromUpdate(TelegramBotUpdate $update): array
    {
	$adminParams = $this->apiDtoFactory->createAdminSendMessageParams($update);
	return $this->sendDto($adminParams);
    }

    public function sendChatAction(string $action, TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createSendChatActionParams($action, $update);
	return $this->sendDto($params);
    }

    public function answerCallbackQuery(TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createAnswerCallbackQueryParams($update);
	return $this->sendDto($params);
    }

    public function sendInlineKeyboard(TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createSendInlineKeyboardParams($update);
	return $this->sendDto($params);
    }

    public function sendCallbackQueryResponse(TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createCallbackQueryParams($update);
	return $this->sendDto($params);
    }

    public function sendGenericMessage(string $message, int $chatId): array
    {
	$params = $this->apiDtoFactory->createGenericSendMessageParams($message, $chatId);
	return $this->sendDto($params);
    }
}

