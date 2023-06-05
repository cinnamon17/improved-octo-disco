<?php

namespace App\Controller;

use App\Service\BotRequestMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(Request $request, BotRequestMethod $botApi, HttpClientInterface $client, ContainerBagInterface $env): JsonResponse
    {
        //$client->request('POST','https://api.openai.com/v1/chat/completions');

        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' =>  "Bearer {$env->get('OPENAI_KEY')}"
            ],
            'json' => [
                        "model" => "gpt-3.5-turbo",
                        "messages" => [["role" => "user", "content" => "Hello!"]]

                      ]
        ]);

        $data = json_decode($response->getContent(false), true);

        $message = $data['choices'][0]['message']['content'];
        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];
        $response = $botApi->apiRequest('POST','/sendMessage', ['chat_id' => $chat_id, 'text' => 'hola']);
        $response = $botApi->apiRequest('POST','/sendMessage', ["chat_id" => $chat_id, "text" => $chat_id]);
        $response = $botApi->apiRequest('POST','/sendMessage', ["chat_id" => $chat_id, "text" => $message]);

        return $this->json($response);

    }
}
