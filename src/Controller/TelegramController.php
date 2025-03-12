<?php

namespace App\Controller;

use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    private TelegramService $tService;

    public function __construct(TelegramService $tService)
    {
        $this->tService = $tService;
    }
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(): JsonResponse
    {

        if ($this->tService->isCallbackQuery()) {
            $this->tService->handleCallbackQuery();
            return $this->json('ok');
        }

        if (!$this->tService->getChatId()) {
            return $this->json('invalid chat_id');
        }

        if (!$this->tService->getMessageText()) {
            return $this->json('invalid message');
        }

        if (!$this->tService->isUserExists()) {
            $this->tService->insertUserInDb();
        }

        if ($this->tService->isUserExists()) {
            $this->tService->updateUserInDb();
        }

        if ($this->tService->getMessageText() == "/start") {
            $response = $this->tService->sendWelcomeMessage();
            return $this->json($response);
        }

        if ($this->tService->getMessageText() == "/mode") {
            $response = $this->tService->sendInlineKeyboard();
            return $this->json($response);
        }

        $openaiResponse = $this->tService->chatCompletion();
        $response = $this->tService->sendMessage($openaiResponse["choices"][0]["message"]["content"]);

        return $this->json($response);
    }
}
