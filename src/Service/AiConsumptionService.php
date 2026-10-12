<?php

namespace App\Service;

use App\Entity\AiUsageLog;
use App\Entity\User;
use Psr\Log\LoggerInterface;

/**
 * Orquesta el ciclo de un request de IA: reserva, ejecucion y liquidacion.
 *
 * Envuelve a AIService, no la sustituye, para que el cobro no se disperse
 * entre el dispatcher y el processor.
 */
class AiConsumptionService
{
    private CreditManager $creditManager;
    private CreditPriceCalculator $calculator;
    private AiTokenCounter $tokenCounter;
    private LoggerInterface $logger;

    public function __construct(
        CreditManager $creditManager,
        CreditPriceCalculator $calculator,
        AiTokenCounter $tokenCounter,
        LoggerInterface $logger,
    ) {
        $this->creditManager = $creditManager;
        $this->calculator = $calculator;
        $this->tokenCounter = $tokenCounter;
        $this->logger = $logger;
    }

    public function getLog(int $id): ?AiUsageLog
    {
        return $this->creditManager->findLog($id);
    }

    public function logFailure(\Throwable $e): void
    {
        $this->logger->error('Fallo en el consumo de IA', [
            'exception' => $e->getMessage(),
            'class' => $e::class,
        ]);
    }

    /**
     * Crea el log y reserva el precio plano. Lanza InsufficientCreditsException
     * si el usuario no llega; el llamador decide que mensaje de Telegram
     * enviarle.
     */
    public function reserve(User $user, ?string $mode, string $telegramMessageId, string $model = 'deepseek-chat'): AiUsageLog
    {
        $log = new AiUsageLog();
        $log->setUser($user)
            ->setMode($mode)
            ->setModel($model)
            ->setTelegramMessageId($telegramMessageId);

        return $this->creditManager->reserve($user, $this->calculator->getRequestPrice(), $log);
    }

    /**
     * Request completado: se cobra el precio plano integro.
     */
    public function complete(AiUsageLog $log, int $receivedTokens): void
    {
        $credits = $this->calculator->settleCredits($log, $receivedTokens, completed: true);

        $log->setTotalTokens($receivedTokens);
        $this->creditManager->settle($log, $credits);
    }

    /**
     * Stream cortado a mitad: cobro proporcional a lo entregado (D2).
     */
    public function settleCutoff(AiUsageLog $log, int $receivedTokens): void
    {
        $credits = $this->calculator->settleCredits($log, $receivedTokens, completed: false);

        $log->setTotalTokens($receivedTokens);
        $this->creditManager->settle($log, $credits);
    }

    /**
     * Fallo de la IA: devolucion integra.
     */
    public function fail(AiUsageLog $log, ?string $error = null): void
    {
        $this->creditManager->void($log, $error);
    }

    /**
     *(tokens 0 en un stream que no fallo del todo) no es un fallo, es un
     * stream vacio: se anula igual que un error para no cobrar por nada.
     */
    public function refundEmpty(AiUsageLog $log): void
    {
        $this->creditManager->void($log, 'Stream vacio: la IA no devolvio contenido');
    }

    public function hasEnoughCredits(User $user): bool
    {
        return $user->hasAtLeast($this->calculator->getRequestPrice());
    }

    public function getRequestPrice(): int
    {
        return $this->calculator->getRequestPrice();
    }

    public function balanceOf(User $user): int
    {
        return $this->creditManager->balanceOf($user);
    }
}