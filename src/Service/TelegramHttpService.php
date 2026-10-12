<?php

namespace App\Service;

use App\Dto\TelegramDtoInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramHttpService implements LoggerAwareInterface
{
    private HttpClientInterface $client;
    private ContainerBagInterface $env;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, ContainerBagInterface $env)
    {
        $this->client = $client;
        $this->env = $env;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function telegramRequest(TelegramDtoInterface $dto): array
    {
        $data = ['json' => $dto->toArray()];
        $telegramMethodUrl = $this->env->get('BOT_API') . $dto->getMethod();
        $response = $this->client->request('POST', $telegramMethodUrl, $data);

        $body = $response->toArray();

        $this->logFailure($dto, $telegramMethodUrl, $body);

        return $body;
    }

    /**
     * Telegram siempre responde 200 con ok=false y la causa en description.
     * Sin esto el motivo se pierde en silencio, que es como un sendInvoice
     * fallido acaba dejando una StarTransaction en PENDING que el
     * pre_checkout_query daria por valida.
     */
    private function logFailure(TelegramDtoInterface $dto, string $url, array $body): void
    {
        if (($body['ok'] ?? true) === true) {
            return;
        }

        $this->logger->error('Telegram API devolvio ok=false', [
            'method' => $dto->getMethod(),
            'error_code' => $body['error_code'] ?? null,
            'description' => $body['description'] ?? 'sin descripcion',
            'parameters' => $body['parameters'] ?? null,
            'url' => $url,
            'request' => $dto->toArray(),
        ]);
    }
}