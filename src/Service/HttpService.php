<?php

namespace App\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\HeadersDto;
use App\Dto\OpenAIDto;
use App\Dto\OpenAIJsonDto;
use App\Dto\OpenAIMessageDto;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpService
{

    private HttpClientInterface $client;
    private ContainerBagInterface $env;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env)
    {

        $this->client = $client;
        $this->env = $env;
    }

    public function chatCompletion(ChatPromptMessageDto $chatDto): array
    {

        $params = $this->requestParams($chatDto);
        $openAIurl = $this->env->get('OPENAI_URL');
        $response = $this->client->request('POST', $openAIurl, $params);

        return $response->toArray();
    }

    public function request(array $params)
    {

        $data = ['json' => $params];
        $telegramMethodUrl = $this->env->get('BOT_API') . $params['method'];
        $response = $this->client->request('POST', $telegramMethodUrl, $data);

        return $response->toArray();
    }

    private function requestParams(ChatPromptMessageDto $chatDto): array
    {

        $headersDto = (new HeadersDto())
            ->setAccept('application/json')
            ->setAuthorization((string) $this->env->get('OPENAI_KEY'));

        $systemPromptOpenAI = (new OpenAIMessageDto())
            ->setRole('system')
            ->setContent($chatDto->getPrompt());

        $userMessageToOpenAI = (new OpenAIMessageDto())
            ->setRole('user')
            ->setContent($chatDto->getMessage());

        $jsonDto = (new OpenAIJsonDto())
            ->setModel('gpt-3.5-turbo')
            ->setMessages([$systemPromptOpenAI, $userMessageToOpenAI]);

        $openAIDto = (new OpenAIDto())
            ->setHeaders($headersDto)
            ->setJson($jsonDto);

        return $openAIDto->toArray();
    }
}
