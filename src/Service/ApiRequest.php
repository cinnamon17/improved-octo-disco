<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiRequest
{
    private ContainerBagInterface $env;
    private HttpClientInterface $client;
    private TelegramBotUpdate $update;

    public function __construct(ContainerBagInterface $containerBagInterface, HttpClientInterface $client, TelegramBotUpdate $update)
    {

        $this->env= $containerBagInterface;
        $this->client = $client;
        $this->update = $update;
    }

    public function telegramApi(String $apiMethod, array $params = [], String $httpMethod = 'POST'): array
    {

        $response = $this->client->request(
            $httpMethod,
            $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/".$apiMethod,
            ['json' => $params]
        );
        $content = $response->toArray(false);

        return $content;
    }

    public function openApi(?string $messageText, ?string $mode = 'asistente'): string
    {

        if(!$messageText) {

            $this->sendErrorMessage('Por favor envia un texto valido');
            die();
        }

        $response = $this->sendChatAction('typing');
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [

            'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' =>  "Bearer {$this->env->get('OPENAI_KEY')}"
            ],
            'json' => [
                        "model" => "gpt-3.5-turbo",
                        "messages" => [
                            ["role" => "system", "content" => "Eres $mode, responde como tal"],
                            ["role" => "user", "content" => $messageText]
                        ]

            ]
        ]);

        $response = $response->toArray(false);
        return $response['choices']['0']['message']['content'] ?? $this->openApi($messageText);

    }

    public function sendRequest($telegramMethod, $params = [], $httpMethod = 'POST'): array
    {

        $response = $this->client->request(
            $httpMethod,
            $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/".$telegramMethod,
            ['json' => $params]
        );
        return $response->toArray(false);
    }

    public function sendMessage(array $params = [], String $httpMethod = 'POST'): array
    {

        if(!$params['text']) {

            $response = $this->sendErrorMessage('Por favor envia un texto valido');
            return $response;
        }

        $response = $this->sendRequest('sendMessage', $params, $httpMethod);
        return $response ?? $this->sendMessage($params);
    }

    public function sendErrorMessage(string $errorMessage, $httpMethod = 'POST'): array
    {

        $response = $this->sendRequest('sendMessage', ['chat_id' => $this->update->getChatId(), 'text' => $errorMessage], $httpMethod);
        return $response;
    }


    public function sendChatAction(string $action = 'typing', $httpMethod = 'POST'): array
    {

        $response =$this->sendRequest('sendChatAction', ['chat_id' => $this->update->getChatId(), 'action' => $action ], $httpMethod);
        return $response;
    }

    public function answerCallbackQuery(string $id, $httpMethod = 'POST'): array
    {

        $response =$this->sendRequest('answerCallbackQuery', ['callback_query_id' => $id ], $httpMethod);
        return $response;
    }


}
