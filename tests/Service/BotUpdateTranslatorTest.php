<?php

namespace App\Tests\Service;

use App\Service\BotUpdateTranslator;
use App\Service\TelegramBotUpdate;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class BotUpdateTranslatorTest extends KernelTestCase
{
    public function testGetAssistantMessageWillReturnInSpanish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('asistente', $but->getAssistantMessage());
    }

    public function testGetAsisstantMessageWillReturnInEnglish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('assistant', $but->getAssistantMessage());
    }

    public function testGetCharacterMessageWillReturnInSpanish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('¿Que modo te gustaria que interpretara? 🎭', $but->getCharacterMessage());
    }

    public function testGetCharacterMessageWillReturnInEnglish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('What mode would you like me to portray? 🎭', $but->getCharacterMessage());
    }

    public function testGetBussinessMessageWillReturnInSpanish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('Ideas de Negocio', $but->getbussinessMessage());
    }

    public function testGetBussinessMessageWillReturnInEnglish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('Business Ideas', $but->getbussinessMessage());
    }

    public function testGetTranslatorMessageWillReturnInSpanish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('traductor', $but->getTranslatorMessage());
    }

    public function testGetTranslatorMessageWillReturnInEnglish(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('en');

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertSame('translator', $but->getTranslatorMessage());
    }

    public function testGetCallbackQueryLanguageCode(): void
    {
        self::bootKernel();

        $translator = static::getContainer()->get(TranslatorInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $telegramBotUpdate->method('getCallbackQuery')
            ->with('from')
            ->willReturn(['language_code' => 'en']);

        $but = new BotUpdateTranslator($telegramBotUpdate, $translator);
        $this->assertEquals('en', $but->getCallbackQueryLanguageCode());
    }
}
