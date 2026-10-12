<?php

namespace App\Service;

use App\Dto\TelegramBotUpdate;
use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramRouter
{
    private TelegramService $tService;
    private TelegramPaymentService $paymentService;

    public function __construct(TelegramService $tService, TelegramPaymentService $paymentService)
    {
	$this->tService = $tService;
	$this->paymentService = $paymentService;
    }

    public function handle(TelegramBotUpdate $update): JsonResponse
    {
	// El orden importa: pre_checkout_query no trae message, y
	// successful_payment no trae text. Cualquier getMessageText() antes de
	// estas ramas revienta con esos updates.
	if ($update->isPreCheckoutQuery()) {
	    return new JsonResponse($this->paymentService->handlePreCheckoutQuery($update));
	}

	if ($update->hasSuccessfulPayment()) {
	    return new JsonResponse($this->paymentService->handleSuccessfulPayment($update));
	}

	if ($update->hasRefundedPayment()) {
	    return new JsonResponse($this->paymentService->handleRefundedPayment($update));
	}

	if ($update->isCallbackQuery()) {
	    return new JsonResponse($this->tService->handleCallbackQuery($update));
	}

	if (!$update->getChatId() || !$update->getMessageText()) {
	    return new JsonResponse(['status' => 'ignored'], 200);
	}

	return new JsonResponse($this->tService->handleIncomingMessage($update));

    }


}