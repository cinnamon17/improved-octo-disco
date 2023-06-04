<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram')]
    public function index(Request $request, HttpClientInterface $client): JsonResponse
    {
        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];

        $response = $client->request('GET','https://api.telegram.org/bot1898926696:AAHyKmf02EF25EPupvGiotboowP6p8i8E6I/sendMessage', [
                                    'json' => ['chat_id' => $chat_id, 'text' => 'hello']]);

        return $this->json($response);

    }
}
