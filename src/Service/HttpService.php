<?php

namespace App\Service;

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

    public function chatCompletion(string $message, string  $prompt): array
    {

        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/chat/completions',
            $this->requestParams($message, $prompt)
        );

        $content = $response->toArray();
        return $content;
    }

    public function request(array $params)
    {

        $response = $this->client->request(
            'POST',
            $this->env->get('BOT_URL') . $this->env->get('BOT_KEY') . "/" . $params['method'],
            [
                'json' => $params
            ]
        );

        return $response->toArray();
    }

    private function requestParams(string $message, string $prompt): array
    {

        $headersDto = $this->createHeadersDto();
        $jsonDto = $this->createOpenAIJsonDto($message, $prompt);

        $openAIDto = new OpenAIDto();
        $openAIDto->setHeaders($headersDto)
            ->setJson($jsonDto);

        return $openAIDto->toArray();
    }

    private function createHeadersDto(): HeadersDto
    {

        $headersDto = new HeadersDto();
        $headersDto->setAccept('application/json')
            ->setAuthorization("Bearer {$this->env->get('OPENAI_KEY')}");
        return $headersDto;
    }

    private function createOpenAIJsonDto(string $message, string $prompt): OpenAIJsonDto
    {

        $system = $this->createSystemMessage($prompt);
        $user = $this->createUserMessage($message);

        $jsonDto = new OpenAIJsonDto();
        $jsonDto->setModel('gpt-3.5-turbo')
            ->setMessages([$system, $user]);

        return $jsonDto;
    }

    private function createSystemMessage(string $prompt): OpenAIMessageDto
    {
        $system = new OpenAIMessageDto();
        $system->setRole('system')
            ->setContent($prompt);

        return $system;
    }

    private function createUserMessage(string $message): OpenAIMessageDto
    {
        $user = new OpenAIMessageDto();
        $user->setRole('user')
            ->setContent($message);
        return $user;
    }
}
