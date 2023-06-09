<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiRequest {

    private ContainerBagInterface $env;
    private HttpClientInterface $client;
    private TelegramBotUpdate $update;

    public function __construct(ContainerBagInterface $containerBagInterface, HttpClientInterface $client, TelegramBotUpdate $update) {

            $this->env= $containerBagInterface;
            $this->client = $client;
            $this->update = $update;
    }

    public function telegramApi(  String $apiMethod, array $params = [], String $httpMethod = 'POST') : array {

        $response = $this->client->request($httpMethod,
        $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/".$apiMethod, ['json' => $params]);
        $content = $response->toArray(false);

        return $content;
    }

    public function sendMessage( array $params = [], String $httpMethod = 'POST') : array {

        if(!$params['text']){

            $error_message = ['chat_id' => $params['message']['chat']['id'], 'text' => 'Por favor envia un texto valido'];
            $response = $this->client->request($httpMethod,
            $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/sendMessage", ['json' => $error_message]);

            return $response->toArray(false);

        }

        $response = $this->client->request($httpMethod,
        $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/sendMessage", ['json' => $params]);
        $content = $response->toArray(false);

        return $content ?? $this->sendMessage($params);
    }

    public function openApi(?String $messageText): string{

        if(!$messageText){

            $error_message = ['chat_id' => $this->update->getChatId(), 'text' => 'Por favor envia un texto valido'];
            $response = $this->client->request('POST',
            $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/sendMessage", ['json' => $error_message]);

            die();
        }

        $this->sendMessage(['chat_id' => $this->update->getChatId(), 'text' => '...']);
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

        $response = $response->toArray(false);

        return $response['choices']['0']['message']['content'] ?? $this->openApi($messageText);

    }

}
