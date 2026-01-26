<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AIHttpService
{
    private HttpClientInterface $client;
    private ContainerBagInterface $env;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env)
    {
        $this->client = $client;
        $this->env = $env;
    }

    /**
     * Envía la solicitud HTTP a la API de IA.
     * @param array $headers Los headers de la solicitud (incluyendo la Auth Key).
     * @param array $json El cuerpo JSON de la solicitud.
     */

    public function sendChatCompletionRequest(array $headers, array $json): ResponseInterface
    {
        $url = $this->env->get('OPENAI_URL');

        $options = [
            'headers' => $headers,
            'json' => $json,
        ];

        return $this->client->request('POST', $url, $options);
    }

    public function getChatCompletionStream(array $header, array $json): iterable
    {
        $url = $this->env->get('OPENAI_URL');

        $options = [
            'headers' => $header,
            'json' => $json,
        ];

        $response =  $this->client->request('POST', $url, $options);

        foreach ($this->client->stream($response) as $chunk) {
            $content = $chunk->getContent();

            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line === 'data: [DONE]' || !str_starts_with($line, 'data: ')) {
                    continue;
                }

                $jsonString = mb_substr($line, 6);
                $data = json_decode($jsonString, true);

                if (isset($data['choices'][0]['delta']['content'])) {
                    yield $data['choices'][0]['delta']['content'];
                }
            }
        }
    }
}
