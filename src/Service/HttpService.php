<?php

namespace App\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\TelegramDtoInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpService
{

    private HttpClientInterface $client;
    private TelegramDtoFactory $dtoFactory;
    private ContainerBagInterface $env;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env, TelegramDtoFactory $dtoFactory, )
    {

        $this->client = $client;
        $this->dtoFactory= $dtoFactory;
	$this->env = $env;
    }

    public function chatCompletion(ChatPromptMessageDto $chatDto): ResponseInterface
    {

        $params = $this->dtoFactory->createRequestAIparams($chatDto);
        $openAIurl = $this->env->get('OPENAI_URL');
        $response = $this->client->request('POST', $openAIurl, $params->toArray());

        return $response;
    }

    public function telegramRequest(TelegramDtoInterface $dto): array
    {

        $data = ['json' => $dto->toArray()];
        $telegramMethodUrl = $this->env->get('BOT_API') . $dto->getMethod();
        $response = $this->client->request('POST', $telegramMethodUrl, $data);

        return $response->toArray();
    }

}
