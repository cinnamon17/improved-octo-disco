<?php

namespace App\Tests\Integration\Service;

use App\Service\BotUpdateTranslator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Las claves de pago tienen que existir en ambos idiomas y con los
 * placeholders correctos: si falta una, el bot responde con la cadena de la
 * clave y el usuario ve "%stars% Stars".
 */
class BotUpdateTranslatorPaymentsTest extends TestCase
{
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = new Translator('en');
        $this->translator->addLoader('yaml', new YamlFileLoader());

        foreach (['es', 'en'] as $locale) {
            $this->translator->addResource(
                'yaml',
                __DIR__ . '/../../../translations/messages.' . $locale . '.yaml',
                $locale
            );
        }
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function translationKeys(): array
    {
        return [
            'balance' => ['balance.message', ['%credits%']],
            'insufficient' => ['credits.insufficient', ['%credits%', '%price%']],
            'topup' => ['topup.message', []],
            'pack button' => ['topup.pack_button', ['%stars%', '%credits%']],
            'invoice title' => ['invoice.title', []],
            'invoice label' => ['invoice.label', ['%stars%']],
            'payment invalid' => ['payment.invalid', []],
            'payment confirmed' => ['payment.confirmed', ['%stars%']],
            'payment refunded' => ['payment.refunded', []],
        ];
    }

    #[DataProvider('translationKeys')]
    public function testKeyExistsInBothLocales(string $key, array $placeholders): void
    {
        foreach (['es', 'en'] as $locale) {
            $translated = $this->translator->trans($key, [], 'messages', $locale);

            $this->assertNotSame($key, $translated, "Falta la clave $key en $locale");
            $this->assertNotEmpty($translated);

            foreach ($placeholders as $placeholder) {
                $this->assertStringContainsString(
                    $placeholder,
                    $translated,
                    "A $key ($locale) le falta el placeholder $placeholder"
                );
            }
        }
    }

    public function testBalanceMessageInterpolatesCredits(): void
    {
        $but = new BotUpdateTranslator($this->translator);

        $this->assertStringContainsString('42', $but->getBalanceMessage('es', 42));
        $this->assertStringContainsString('42', $but->getBalanceMessage('en', 42));
    }

    public function testInsufficientMessageInterpolatesBalanceAndPrice(): void
    {
        $but = new BotUpdateTranslator($this->translator);

        $message = $but->getInsufficientCreditsMessage('es', 1, 2);

        $this->assertStringContainsString('1', $message);
        $this->assertStringContainsString('2', $message);
        $this->assertStringNotContainsString('%credits%', $message);
        $this->assertStringNotContainsString('%price%', $message);
    }

    public function testPackButtonShowsCreditsAtConfiguredRatio(): void
    {
        // Ratio 1: 10 Stars son 10 creditos.
        $but = new BotUpdateTranslator($this->translator, [10, 25, 50], 1);

        $this->assertSame([10, 25, 50], $but->getTopUpPacks());

        $button = $but->getPackButtonText('es', 25);
        $this->assertStringContainsString('25', $button);
        $this->assertStringContainsString('25', $button);
    }

    public function testPackButtonAppliesStarsToCreditsRatio(): void
    {
        // Ratio 2: 25 Stars son 50 creditos.
        $but = new BotUpdateTranslator($this->translator, [25], 2);

        $button = $but->getPackButtonText('en', 25);

        $this->assertStringContainsString('25', $button);
        $this->assertStringContainsString('50', $button);
    }

    public function testPaymentInvalidMessageIsNotTheKeyItself(): void
    {
        $but = new BotUpdateTranslator($this->translator);

        $this->assertNotSame('payment.invalid', $but->getPaymentInvalidMessage('es'));
        $this->assertNotSame('payment.invalid', $but->getPaymentInvalidMessage('en'));
    }
}