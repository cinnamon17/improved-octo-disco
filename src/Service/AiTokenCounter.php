<?php

namespace App\Service;

/**
 * Estimador de tokens de un fragmento de stream.
 *
 * Con precio plano no se busca precision contable: solo hace falta una
 * magnitud para decidir entre los tres niveles de cobro de CreditPriceCalculator.
 * La regla de 4 caracteres por token es la habitual para español, que tokeniza
 * peor que ingles por los acentos y la punctuacion.
 */
class AiTokenCounter
{
    private const CHARS_PER_TOKEN = 4;

    public function count(string $chunk): int
    {
        return (int) ceil(mb_strlen($chunk) / self::CHARS_PER_TOKEN);
    }

    public function sum(iterable $chunks): int
    {
        $tokens = 0;

        foreach ($chunks as $chunk) {
            $tokens += $this->count($chunk);
        }

        return $tokens;
    }
}