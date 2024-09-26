<?php

namespace App\Tests\Integration\Service;

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
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->translator->method('translate')
            ->willReturn('asistente');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('asistente', $but->getAssistantMessage());
    }

    public function testGetAsisstantMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $this->translator->method('translate')
            ->willReturn('assistant');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('assistant', $but->getAssistantMessage());
    }

    public function testGetCharacterMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->translator->method('translate')
            ->willReturn('¿Que modo te gustaria que interpretara? 🎭');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('¿Que modo te gustaria que interpretara? 🎭', $but->getCharacterMessage());
    }

    public function testGetCharacterMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $this->translator->method('translate')
            ->willReturn('What mode would you like me to portray? 🎭');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('What mode would you like me to portray? 🎭', $but->getCharacterMessage());
    }

    public function testGetBussinessMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->translator->method('translate')
            ->willReturn('Ideas de Negocio');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Ideas de Negocio', $but->getbussinessMessage());
    }

    public function testGetBussinessMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $this->translator->method('translate')
            ->willReturn('Business Ideas');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('Business Ideas', $but->getbussinessMessage());
    }

    public function testGetTranslatorMessageWillReturnInSpanish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->translator->method('translate')
            ->willReturn('traductor');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('traductor', $but->getTranslatorMessage());
    }

    public function testGetTranslatorMessageWillReturnInEnglish(): void
    {
        $this->telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $this->translator->method('translate')
            ->willReturn('translator');

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertSame('translator', $but->getTranslatorMessage());
    }

    public function testGetCallbackQueryLanguageCode(): void
    {
        $this->telegramBotUpdate->method('getCallbackQuery')
            ->with('from')
            ->willReturn(['language_code' => 'en']);

        $but = new BotUpdateTranslator($this->telegramBotUpdate, $this->translator);
        $this->assertEquals('en', $but->getCallbackQueryLanguageCode());
    }
}
