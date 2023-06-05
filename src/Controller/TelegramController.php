<?php

namespace App\Controller;

use App\Service\BotRequestMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(Request $request, BotRequestMethod $botApi): JsonResponse
    {
        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];
        $botApi->apiRequest('POST','/sendMessage', ['chat_id' => $chat_id, 'text' => 'hello']);

        return $this->json($botApi->getResponse());

    }
}
