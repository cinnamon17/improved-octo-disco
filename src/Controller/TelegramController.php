<?php

namespace App\Controller;

use App\Service\TelegramBotUpdate;
use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    private TelegramService $tService;
    private TelegramBotUpdate $update;

    public function __construct(TelegramService $tService, TelegramBotUpdate $update)
    {
        $this->tService = $tService;
        $this->update = $update;
    }

    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(): JsonResponse
    {

        if ($this->update->isCallbackQuery()) {
            $this->tService->handleCallbackQuery();
            return $this->json('ok');
        }

        if (!$this->update->getChatId()) {
            return $this->json('invalid chat_id');
        }

        if (!$this->update->getMessageText()) {
            return $this->json('invalid message');
        }

	$response = $this->tService->handleIncomingMessage();
        return $this->json($response);
    }
}
