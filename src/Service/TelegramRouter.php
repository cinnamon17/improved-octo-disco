<?php

namespace App\Service;

use App\Dto\TelegramBotUpdate;
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
	    return new JsonResponse($this->tService->handleCallbackQuery());
	}

	if (!$update->getChatId() || !$update->getMessageText()) {
	    return new JsonResponse(['status' => 'ignored'], 200);
	}

	return new JsonResponse($this->tService->handleIncomingMessage($update));

    }


}
