<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Verifica X-Telegram-Bot-Api-Secret-Token.
 *
 * Falla cerrado a proposito: sin secret token, /telegram acepta POST de
 * cualquiera, y con pagos activo eso significa que un successful_payment
 * falsificado acredita creditos.
 */
class TelegramSecretTokenValidator
{
    private string $secretToken;
    private LoggerInterface $logger;

    public function __construct(string $secretToken, LoggerInterface $logger)
    {
        $this->secretToken = $secretToken;
        $this->logger = $logger;
    }

    public function isValid(?string $provided): bool
    {
        if ($this->secretToken === '') {
            $this->logger->critical('TELEGRAM_SECRET_TOKEN no configurado: se rechazan todos los webhooks', [
                'hint' => 'Anade el secret a GitHub Actions y recompila con composer dump-env prod',
            ]);

            return false;
        }

        if ($provided === null) {
            return false;
        }

        return hash_equals($this->secretToken, $provided);
    }
}