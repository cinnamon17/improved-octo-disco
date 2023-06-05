<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiRequest {

    private ContainerBagInterface $env;
    private HttpClientInterface $client;

    public function __construct(ContainerBagInterface $containerBagInterface, HttpClientInterface $client) {

            $this->env= $containerBagInterface;
            $this->client = $client;
    }

    public function telegramApi( String $httpMethod, String $apiMethod, array $params = []) : array {

        $response = $this->client->request($httpMethod,
        $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/".$apiMethod, ['json' => $params]);
        $content = $response->toArray(false);

        return $content;
    }

    public function openApi(String $messageText): array {

        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [

            'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' =>  "Bearer {$this->env->get('OPENAI_KEY')}"
            ],
            'json' => [
                        "model" => "gpt-3.5-turbo",
                        "messages" => [["role" => "user", "content" => $messageText]]

            ]
        ]);

        return $response->toArray(false);

    }

}
