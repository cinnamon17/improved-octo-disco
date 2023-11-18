<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpService {

    private HttpClientInterface $client;
    private ContainerBagInterface $env;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env){

        $this->client = $client;
        $this->env = $env;

    }

    public function chatCompletion(string $message, string  $prompt ):array {

        $response = $this->client->request('POST',
            'https://api.openai.com/v1/chat/completions',$this->requestParams($message, $prompt)
        );

        $content = $response->toArray();
        return $content;
    }

    public function request(array $params){

        $response = $this->client->request('POST',
            $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/".$params['method'],[
            'json' => $params
        ]);

        return $response->toArray();

    }

    private function requestParams( string $message, string $prompt):array {
        $params = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' =>  "Bearer {$this->env->get('OPENAI_KEY')}"
            ],
            'json' => [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $prompt ],
                    ["role" => "user", "content" => $message ]
                ]
            ]
        ];

        return $params;
    }



}
