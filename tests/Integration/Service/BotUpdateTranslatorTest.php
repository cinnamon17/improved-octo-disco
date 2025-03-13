<?php

namespace App\Tests\Integration\Service;

use App\Service\BotUpdateTranslator;
use App\Service\TelegramBotUpdate;
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
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('asistente', $but->getAssistantMessage());
    }

    public function testGetAsisstantMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('assistant', $but->getAssistantMessage());
    }

    public function testGetCharacterMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('¿Que modo te gustaria que interpretara? 🎭', $but->getCharacterMessage());
    }

    public function testGetCharacterMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('What mode would you like me to portray? 🎭', $but->getCharacterMessage());
    }

    public function testGetBussinessMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Ideas de Negocio', $but->getBusinessMessage());
    }

    public function testGetBussinessMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Business Ideas', $but->getBusinessMessage());
    }

    public function testGetTranslatorMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('traductor', $but->getTranslatorMessage());
    }

    public function testGetTranslatorMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('translator', $but->getTranslatorMessage());
    }

    public function testGetWelcomeMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $message = "Hello!";

        $this->assertStringContainsString($message, $but->getWelcomeMessage());
    }

    public function testGetWelcomeMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $message = "¡Hola!";

        $this->assertStringContainsString($message, $but->getWelcomeMessage());
    }
}
