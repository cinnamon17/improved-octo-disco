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
	    return $this->tService->handleCallbackQuery();
	}

	if (!$update->getChatId() || !$update->getMessageText()) {
	    return new JsonResponse(['status' => 'ignored'], 200);
	}

	return $this->tService->handleIncomingMessage();
    }


}
