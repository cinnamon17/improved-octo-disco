<?php

namespace App\Controller;

use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    private TelegramService $tService;
    private TelegramBotUpdate $update;
    private DBService $db;

    public function __construct(TelegramService $tService, TelegramBotUpdate $update, DBService $db)
    {
        $this->tService = $tService;
        $this->update = $update;
        $this->db = $db;
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

        if (!$this->db->isUserExists()) {
            $this->db->insertUserInDb();
        }

        if ($this->db->isUserExists()) {
            $this->db->updateUserInDb();
        }

        if ($this->update->getMessageText() == "/start") {
            $response = $this->tService->sendWelcomeMessage();
            return $this->json($response);
        }

        if ($this->update->getMessageText() == "/mode") {
            $response = $this->tService->sendInlineKeyboard();
            return $this->json($response);
        }

        $openaiResponse = $this->tService->chatCompletion();
        $response = $this->tService->sendMessage($openaiResponse["choices"][0]["message"]["content"]);

        return $this->json($response);
    }
}
