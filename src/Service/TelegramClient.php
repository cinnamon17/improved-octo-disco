<?php

namespace App\Service;

use App\Dto\AnswerPreCheckoutQueryDto;
use App\Dto\RefundStarPaymentDto;
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

    /**
     * Keyboard de recarga. Cada boton lleva data "topup:<stars>", que el
     * callback_query existente despacha a TelegramPaymentService.
     */
    public function sendTopUpKeyboard(TelegramBotUpdate $update): array
    {
	$params = $this->apiDtoFactory->createTopUpKeyboardParams($update);

	return $this->sendDto($params);
    }

    /**
     * Traduce y envia el aviso de saldo insuficiente. El texto lo compone
     * TelegramService, que es quien tiene el BotUpdateTranslator.
     */
    public function sendInsufficientCreditsMessage(TelegramBotUpdate $update, string $text): array
    {
	return $this->sendGenericMessage($text, $update->getChatId());
    }

    public function sendInvoice(
        int $chatId,
        string $payload,
        string $title,
        string $description,
        int $stars,
        string $label,
    ): array {
	$params = $this->apiDtoFactory->createSendInvoiceParams(
	    $chatId,
	    $payload,
	    $title,
	    $description,
	    $stars,
	    $label
	);
	return $this->sendDto($params);
    }

    public function answerPreCheckoutQuery(AnswerPreCheckoutQueryDto $dto): array
    {
	return $this->sendDto($dto);
    }

    /**
     * Devuelve las Stars al usuario. Se llama tras un refunded_payment, que es
     * cuando Telegram ya ha hecho el reembolso por su cuenta.
     */
    public function refundStarPayment(int $userId, string $telegramPaymentChargeId): array
    {
	$params = (new RefundStarPaymentDto())
	    ->setUserId($userId)
	    ->setTelegramPaymentChargeId($telegramPaymentChargeId);

	return $this->sendDto($params);
    }

    public function editMessageText(string $message, int $chatId, int $messageId): array
    {
    $params = $this->apiDtoFactory->createEditMessageParams($message, $chatId, $messageId);
    return $this->sendDto($params);
    }
}

