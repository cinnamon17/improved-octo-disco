<?php

namespace App\Service;

use App\Dto\ChatPromptMessageDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramMessageProcessor
{
    private HttpService $http;
    private TelegramService $tService;

    public function __construct(HttpService $http, TelegramService $tService)
    {
	$this->http = $http;
	$this->tService = $tService;
    }

    public function __invoke(ChatPromptMessageDto $chatDto): void
    {
	$response = $this->http->chatCompletion($chatDto)->toArray();

	if (strlen($response["choices"][0]["message"]["content"]) < 4096) {
	    $this->tService->sendMessage($response["choices"][0]["message"]["content"]);
	} else {
	    $chunks = str_split($response["choices"][0]["message"]["content"], 4096);
	    foreach ($chunks as $text) {
		$this->tService->sendMessage($text);
	    }
	}
	
    }
}
