<?php

namespace App\Tests\Unit\Service;

use App\Service\BotUpdateTranslator;
use App\Service\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslatorTest extends TestCase
{

    private TranslatorInterface $translator;
    private TelegramBotUpdate $telegramBotUpdate;

    protected function setUp(): void
    {
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
    }
    public function testGetAssistantMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $this->translator->method('trans')
            ->willReturn('asistente');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('asistente', $but->getAssistantMessage());
    }

    public function testGetAsisstantMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $this->translator->method('trans')
            ->willReturn('assistant');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('assistant', $but->getAssistantMessage());
    }

    public function testGetCharacterMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $this->translator->method('trans')
            ->willReturn('¿Que modo te gustaria que interpretara? 🎭');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('¿Que modo te gustaria que interpretara? 🎭', $but->getCharacterMessage());
    }

    public function testGetCharacterMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $this->translator->method('trans')
            ->willReturn('What mode would you like me to portray? 🎭');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('What mode would you like me to portray? 🎭', $but->getCharacterMessage());
    }

    public function testGetBussinessMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $this->translator->method('trans')
            ->willReturn('Ideas de Negocio');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Ideas de Negocio', $but->getbussinessMessage());
    }

    public function testGetBussinessMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $this->translator->method('trans')
            ->willReturn('Business Ideas');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Business Ideas', $but->getbussinessMessage());
    }

    public function testGetTranslatorMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $this->translator->method('trans')
            ->willReturn('traductor');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('traductor', $but->getTranslatorMessage());
    }

    public function testGetTranslatorMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLocale')
            ->willReturn('en');

        $this->translator->method('trans')
            ->willReturn('translator');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('translator', $but->getTranslatorMessage());
    }
}
