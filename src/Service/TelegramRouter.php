<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class TelegramRouter
{
    private TelegramService $tService;

    public function __construct(TelegramService $tService)
    {
	$this->tService = $tService;
    }

    public function handle(TelegramBotUpdate $update): JsonResponse
    {
	if ($update->isCallbackQuery()) {
	    // Llama a la lógica de callback
	    return $this->tService->handleCallbackQuery();
	}

	if (!$update->getChatId() || !$update->getMessageText()) {
	    // Lógica de validación/ignorancia/logging
	    return new JsonResponse(['status' => 'ignored'], 200);
	}

	// Llama a la lógica de mensaje
	return $this->tService->handleIncomingMessage();
    }


}
