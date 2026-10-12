<?php

namespace App\Service;

use App\Entity\AiUsageLog;
use App\Entity\StarTransaction;

/**
 * Politica de precios. Sin I/O ni estado: toda la aritmetica de creditos
 * vive aqui, que es lo que la hace trivial de testear.
 *
 * Decisiones documentadas:
 *  - Precio plano por request (no se parsea usage del stream).
 *  - Proporcionalidad discreta al corte: tres niveles, no un porcentaje
 *    flotante. El saldo sigue siendo entero y legible, y el usuario que ve
 *    "cobro 1 credito" nunca discrepa.
 *  - El precio por request arranca en 2 porque con 1 el ceil() de la
 *    fraccion parcial (0.5) saturaria a 1 y la proporcionalidad seria
 *    indistinguible del cobro completo.
 */
class CreditPriceCalculator
{
    private int $starsToCredits;
    private int $requestPrice;
    private int $fullChargeTokens;
    private int $minChargeableTokens;

    public function __construct(
        int $starsToCredits = 1,
        int $requestPrice = 2,
        int $fullChargeTokens = 800,
        int $minChargeableTokens = 10,
    ) {
        $this->starsToCredits = $starsToCredits;
        $this->requestPrice = $requestPrice;
        $this->fullChargeTokens = $fullChargeTokens;
        $this->minChargeableTokens = $minChargeableTokens;
    }

    public function starsToCredits(int $stars): int
    {
        return $stars * $this->starsToCredits;
    }

    public function getRequestPrice(): int
    {
        return $this->requestPrice;
    }

    /**
     * Fraccion del precio plano que corresponde a un stream cortado.
     * 1.0 completo, 0.5 parcial, 0.0 nada.
     */
    public function cutoffFraction(int $receivedTokens): float
    {
        if ($receivedTokens >= $this->fullChargeTokens) {
            return 1.0;
        }

        if ($receivedTokens >= $this->minChargeableTokens) {
            return 0.5;
        }

        return 0.0;
    }

    /**
     * Lo que se cobra por un request que llego a completarse.
     */
    public function priceForCompletedRequest(): int
    {
        return $this->requestPrice;
    }

    /**
     * Lo que se cobra por un stream que se corto a mitad. El usuario ya leyo
     * parte del texto, asi que cobra proporcional, nunca el precio completo.
     */
    public function priceForCutoff(int $receivedTokens): int
    {
        $credits = (int) ceil($this->requestPrice * $this->cutoffFraction($receivedTokens));

        return min($credits, $this->requestPrice);
    }

    /**
     * Resuelve el cobro de un log ya reservado. Decide entre las tres
     * ramas sin que el llamador tenga que conocer los umbrales.
     */
    public function settleCredits(AiUsageLog $log, int $receivedTokens, bool $completed): int
    {
        if ($completed) {
            return $this->priceForCompletedRequest();
        }

        return $this->priceForCutoff($receivedTokens);
    }
}