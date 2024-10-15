<?php

namespace App\Tests\Unit\Service;

use App\Dto\CallbackQueryDto;
use App\Dto\ChatDto;
use App\Dto\MessageDto;
use App\Dto\UpdateDto;
use App\Dto\UserDto;
use App\Service\TelegramBotUpdate;
use PHPUnit\Framework\TestCase;

class TelegramBotUpdateTest extends TestCase
{
    private UpdateDto $updateDto;

    protected function setUp(): void
    {

        $this->updateDto = new UpdateDto();
        $callbackQuery = new CallbackQueryDto();
        $userDto = new UserDto();
        $messageDto = new MessageDto();
        $chatDto = new ChatDto();

        $userDto
            ->setFirstName('pepe')
            ->setLastName('dior')
            ->setId(9223372036854775807)
            ->setUsername('TestUsername')
            ->setLanguageCode('es')
            ->setIsBot(false);

        $callbackQuery
            ->setId('4382bfdwdsb323b2d9')
            ->setFrom($userDto)
            ->setData('Data from button callback')
            ->setInlineMessageId('1234csdbsk4839');

        $chatDto
            ->setId(9223372036854775807)
            ->setFirstName('pepe')
            ->setLastName('dior')
            ->setUsername('pepedior');

        $messageDto
            ->setMessageId(2239818)
            ->setFrom($userDto)
            ->setChat($chatDto)
            ->setDate(1686165587)
            ->setText('Cual es la masa de la tierra');

        $this->updateDto
            ->setUpdateId(829824026)
            ->setCallbackQuery($callbackQuery)
            ->setMessage($messageDto);
    }

    public function testGetUpdateId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals(829824026, $telegramBotUpdate->getUpdateId());
        $this->assertNotEquals(829824025, $telegramBotUpdate->getUpdateId());
    }

    public function testGetMessageText(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('Cual es la masa de la tierra', $telegramBotUpdate->getMessageText());
        $this->assertNotEquals(' ', $telegramBotUpdate->getMessageText());
        $this->assertIsString($telegramBotUpdate->getMessageText());
    }

    public function testGetMessageId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals(2239818, $telegramBotUpdate->getMessageId());
        $this->assertNotEquals(2239817, $telegramBotUpdate->getMessageId());
    }
    public function testGetChatId(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals(9223372036854775807, $telegramBotUpdate->getChatId());
        $this->assertNotEquals(1111111112, $telegramBotUpdate->getChatId());
    }

    public function testGetIsBot(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals(false, $telegramBotUpdate->getIsBot());
        $this->assertIsBool($telegramBotUpdate->getIsBot());
    }
    public function testGetFirstName(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('pepe', $telegramBotUpdate->getFirstName());
        $this->assertIsString($telegramBotUpdate->getFirstName());
    }

    public function testGetLastname(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('dior', $telegramBotUpdate->getLastName());
        $this->assertIsString($telegramBotUpdate->getLastName());
    }

    public function testGetUsername(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('TestUsername', $telegramBotUpdate->getUsername());
        $this->assertIsString($telegramBotUpdate->getUsername());
    }

    public function testGetCallbackQuery(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('Data from button callback', $telegramBotUpdate->getCallbackQuery('data'));
        $this->assertIsString($telegramBotUpdate->getCallbackQuery('data'));
    }

    public function testGetLanguageCode(): void
    {
        $telegramBotUpdate = new TelegramBotUpdate($this->updateDto);
        $this->assertEquals('es', $telegramBotUpdate->getLanguageCode());
        $this->assertIsString($telegramBotUpdate->getLanguageCode());
    }
}
