<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Panther\Client;

class ApiRequest
{
    private ContainerBagInterface $env;
    private HttpClientInterface $client;
    private TelegramBotUpdate $update;
    private TranslatorInterface $translator;
    private string $invalidMessage;

    public function __construct(TranslatorInterface $translator, ContainerBagInterface $containerBagInterface, HttpClientInterface $client, TelegramBotUpdate $update)
    {

        $this->env= $containerBagInterface;
        $this->client = $client;
        $this->update = $update;
        $this->translator = $translator;
        $this->invalidMessage = $this->translator->trans('invalid.message', locale: $this->update->getLanguageCode());
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

    public function openApi(?string $messageText, ?string $prompt = 'asistente'): string
    {

        if(!$messageText) {

            $this->sendErrorMessage($this->invalidMessage);
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
                            ["role" => "system", "content" => $prompt],
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

            $response = $this->sendErrorMessage($this->invalidMessage);
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

    public function sendVideo(string $url, $httpMethod = 'POST'): array
    {
        if (filter_var($url, FILTER_VALIDATE_URL) == false){
            $this->sendErrorMessage('url no valida');
            die();
        }

        $client = Client::createFirefoxClient();

        $client->request('GET', $url);

        $crawler = $client->waitFor('video');

        $html = htmlspecialchars_decode($crawler->html());

        $matches = '';
        preg_match('/<video[^>]+src="([^"]+)/', $html, $matches);

        if(!$matches[1]){

            $this->sendErrorMessage('url no encontrada');
            die();
        }
            $this->sendChatAction('upload_video');
            $response = $this->client->request('GET', $matches[1]);

            $formFields = [
                'video' => new DataPart($response->getContent(), 'file.mp4', 'video/mp4'),
                'chat_id' => "{$this->update->getChatId()}"
            ];
            $formData = new FormDataPart($formFields);
            $response = $this->client->request($httpMethod, $this->env->get('BOT_URL').$this->env->get('BOT_KEY')."/sendVideo", [

            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
            ])
            ;
        return $response->toArray();
    }

    public function answerCallbackQuery(string $id, $httpMethod = 'POST'): array
    {

        $response =$this->sendRequest('answerCallbackQuery', ['callback_query_id' => $id ], $httpMethod);
        return $response;
    }


}
