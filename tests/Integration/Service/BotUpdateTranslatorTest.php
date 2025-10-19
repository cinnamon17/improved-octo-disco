<?php

namespace App\Tests\Integration\Service;

use App\Service\BotUpdateTranslator;
use App\Dto\TelegramBotUpdate;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslatorTest extends KernelTestCase
{

    private TranslatorInterface $translator;
    private TelegramBotUpdate $telegramBotUpdate;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->translator = static::getContainer()->get(TranslatorInterface::class);
        $this->telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
    }
    public function testGetAssistantMessageWillReturnInSpanish(): void
    {
	$language = 'es';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('asistente', $but->getAssistantMessage($language));
    }

    public function testGetAsisstantMessageWillReturnInEnglish(): void
    {
	$language = 'en';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('assistant', $but->getAssistantMessage($language));
    }

    public function testGetCharacterMessageWillReturnInSpanish(): void
    {
	$language = 'es';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('¿Que modo te gustaria que interpretara? 🎭', $but->getCharacterMessage($language));
    }

    public function testGetCharacterMessageWillReturnInEnglish(): void
    {
	$language = 'en';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('What mode would you like me to portray? 🎭', $but->getCharacterMessage($language));
    }

    public function testGetBussinessMessageWillReturnInSpanish(): void
    {
	$language = 'es';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('Ideas de Negocio', $but->getBusinessMessage($language));
    }

    public function testGetBussinessMessageWillReturnInEnglish(): void
    {
	$language = 'en';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('Business Ideas', $but->getBusinessMessage($language));
    }

    public function testGetTranslatorMessageWillReturnInSpanish(): void
    {
	$language = 'es';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('traductor', $but->getTranslatorMessage($language));
    }

    public function testGetTranslatorMessageWillReturnInEnglish(): void
    {
	$language = 'en';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $this->assertSame('translator', $but->getTranslatorMessage($language));
    }

    public function testGetWelcomeMessageWillReturnInEnglish(): void
    {
	$language = 'en';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $message = "Hello!";

        $this->assertStringContainsString($message, $but->getWelcomeMessage($language));
    }

    public function testGetWelcomeMessageWillReturnInSpanish(): void
    {
	$language = 'es';
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn($language);

        $but = new BotUpdateTranslator( $this->translator, $this->telegramBotUpdate->getLocale());
        $message = "¡Hola!";

        $this->assertStringContainsString($message, $but->getWelcomeMessage($language));
    }
}
