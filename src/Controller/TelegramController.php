<?php

namespace App\Controller;

use App\Service\ApiRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(Request $request, ApiRequest $apiRequest): JsonResponse
    {

        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];
        $messageText = $update['message']['text'];

        $welcomeMessage = "¡Hola! Soy tu asistente de IA en Telegram. Estoy aquí para ayudarte en todo lo que necesites.\n
Si tienes preguntas, curiosidades o simplemente quieres charlar, ¡no dudes en escribirme! Estoy emocionado de comenzar esta aventura contigo.\n
¡Vamos a explorar el mundo de la inteligencia artificial juntos!";

        if($messageText == "/start"){

            $telegramResponse = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => $welcomeMessage]);
            die();
        }

        $response = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => '...']);
        $openaiResponse = $apiRequest->openApi($messageText);
        $message = $openaiResponse['choices'][0]['message']['content'];
        $response = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => $message]);

        return $this->json($response);

    }
}
