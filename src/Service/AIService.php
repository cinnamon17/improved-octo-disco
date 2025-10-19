<?php
namespace App\Service;

use App\Dto\ChatPromptMessageDto;

class AIService
{
    private AIHttpService $http;
    private TelegramApiDtoFactory $dtoFactory;
    
    public function __construct(AIHttpService $http, TelegramApiDtoFactory $dtoFactory)
    {
        $this->http = $http;
        $this->dtoFactory = $dtoFactory;
    }

    public function getChatCompletion(ChatPromptMessageDto $chatDto): string
    {
	$openAIDto = $this->dtoFactory->createRequestAIparams($chatDto);

	$response = $this->http->sendChatCompletionRequest(
	    $openAIDto->getHeaders()->toArray(), 
	    $openAIDto->getJson()->toArray()
	);

	$responseData = $response->toArray();
	return $responseData["choices"][0]["message"]["content"] ?? 'Error: No se recibió respuesta.';
    }
}
