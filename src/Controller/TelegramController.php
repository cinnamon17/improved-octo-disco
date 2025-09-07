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

        if (!$this->tService->isUserExists()) {
            $this->tService->insertUserInDb();
        }

        if ($this->tService->isUserExists()) {
            $this->tService->updateUserInDb();
        }

        if ($this->update->getMessageText() == "/start") {
            $response = $this->tService->sendWelcomeMessage();
            return $this->json($response);
        }

        if ($this->update->getMessageText() == "/mode") {
            $response = $this->tService->sendInlineKeyboard();
            return $this->json($response);
        }

	$response = new JsonResponse(data: ["request" => "success"]);
	$response->send();

        $openaiResponse = $this->tService->chatCompletion();
	if(strlen($openaiResponse["choices"][0]["message"]["content"]) < 4096){
	    $response = $this->tService->sendMessage($openaiResponse["choices"][0]["message"]["content"]);
	}else{
	    $chunks = str_split($openaiResponse["choices"][0]["message"]["content"], 4096);
	    foreach($chunks as $text){
		$response = $this->tService->sendMessage($text);
	    };
	}

        return $this->json($response);
    }
}
