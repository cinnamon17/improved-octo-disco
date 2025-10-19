<?php

namespace App\Service;

use App\Dto\TelegramDtoInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramHttpService
{

    private HttpClientInterface $client;
    private ContainerBagInterface $env;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env)
    {

        $this->client = $client;
	$this->env = $env;

    }

    public function telegramRequest(TelegramDtoInterface $dto): array
    {

        $data = ['json' => $dto->toArray()];
        $telegramMethodUrl = $this->env->get('BOT_API') . $dto->getMethod();
        $response = $this->client->request('POST', $telegramMethodUrl, $data);

        return $response->toArray();
    }

}
