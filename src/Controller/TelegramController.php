<?php

namespace App\Controller;

use App\Service\TelegramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(TelegramService $telegramService): JsonResponse
    {

        if($telegramService->isCallbackQuery()){
            $telegramService->handleCallbackQuery();
        }

        if(!$telegramService->getChatId()){
            return $this->json('invalid chat_id');
        }

        if(!$telegramService->getMessageText()){
            return $this->json('invalid message');
        }

        if(!$telegramService->isUserExists()){
            $telegramService->insertUserInDb();
        }

        if($telegramService->isUserExists()){
            $telegramService->updateUserInDb();
        }

        if($telegramService->getMessageText() == "/start") {
            $response = $telegramService->sendWelcomeMessage();
            return $this->json($response);
        }

        if($telegramService->getMessageText() == "/mode") {
            $response = $telegramService->sendInlineKeyboard();
            return $this->json($response);
        }

        $openaiResponse = $telegramService->chatCompletion($telegramService->getMessageText());
        $response = $telegramService->sendMessage($openaiResponse["choices"][0]["message"]["content"]);

        return $this->json($response);

    }
}
