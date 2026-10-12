<?php

namespace App\Tests\Unit\Service;

use App\Entity\AiUsageLog;
use App\Service\CreditPriceCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Politica de precios: precio plano por request (D1) y cobro proporcional al
 * corte en tres niveles (D2).
 */
class CreditPriceCalculatorTest extends TestCase
{
    private function calculator(): CreditPriceCalculator
    {
        // 2 creditos por request es el minimo que hace observable la
        // proporcionalidad: con 1 el ceil() de la fraccion 0.5 saturaria a 1.
        return new CreditPriceCalculator(1, 2, 800, 10);
    }

    public function testStarsToCredits(): void
    {
        $this->assertSame(10, $this->calculator()->starsToCredits(10));
    }

    public function testRequestPriceIsTwoCredits(): void
    {
        $this->assertSame(2, $this->calculator()->getRequestPrice());
    }

    public function testCompletedRequestChargesFullPrice(): void
    {
        $this->assertSame(2, $this->calculator()->priceForCompletedRequest());
    }

    public function testCutoffAboveFullThresholdChargesFullPrice(): void
    {
        $this->assertSame(1.0, $this->calculator()->cutoffFraction(800));
        $this->assertSame(1.0, $this->calculator()->cutoffFraction(5000));
        $this->assertSame(2, $this->calculator()->priceForCutoff(800));
    }

    public function testCutoffBetweenThresholdsChargesHalf(): void
    {
        $this->assertSame(0.5, $this->calculator()->cutoffFraction(10));
        $this->assertSame(0.5, $this->calculator()->cutoffFraction(799));
        $this->assertSame(1, $this->calculator()->priceForCutoff(400));
    }

    public function testCutoffBelowMinimumChargesNothing(): void
    {
        $this->assertSame(0.0, $this->calculator()->cutoffFraction(9));
        $this->assertSame(0.0, $this->calculator()->cutoffFraction(0));
        $this->assertSame(0, $this->calculator()->priceForCutoff(9));
    }

    public function testCutoffNeverExceedsFullPrice(): void
    {
        $calculator = $this->calculator();

        $this->assertLessThanOrEqual(
            $calculator->getRequestPrice(),
            $calculator->priceForCutoff(2000)
        );
    }

    public function testSettleCreditsOnCompletedRequest(): void
    {
        $log = (new AiUsageLog())->setCreditsReserved(2);

        $this->assertSame(2, $this->calculator()->settleCredits($log, 900, completed: true));
    }

    public function testSettleCreditsOnCutoffIsProportional(): void
    {
        $log = (new AiUsageLog())->setCreditsReserved(2);

        $this->assertSame(1, $this->calculator()->settleCredits($log, 400, completed: false));
    }

    public function testSettleCreditsOnEmptyStreamIsZero(): void
    {
        $log = (new AiUsageLog())->setCreditsReserved(2);

        $this->assertSame(0, $this->calculator()->settleCredits($log, 0, completed: false));
    }

    public function testStarsToCreditsRatioIsConfigurable(): void
    {
        $calculator = new CreditPriceCalculator(3, 2, 800, 10);

        $this->assertSame(30, $calculator->starsToCredits(10));
    }
}