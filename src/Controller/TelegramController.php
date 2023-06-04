<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram')]
    public function index(Request $request, HttpClientInterface $client, ContainerBagInterface $env): JsonResponse
    {
        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];
        $bot =$env->get('BOT_KEY');

        $response = $client->request('POST',"https://api.telegram.org/$bot/sendMessage", [
                                    'json' => ['chat_id' => $chat_id, 'text' => 'hello']]);

        return $this->json($response);

    }
}
