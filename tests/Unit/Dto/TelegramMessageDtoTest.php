<?php

namespace App\Tests\Unit\Dto;

use App\Dto\TelegramMessageDto;
use PHPUnit\Framework\TestCase;

class TelegramMessageDtoTest extends TestCase
{
    public function testGetMethod(): void
    {
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setMethod('sendMessage');
        $this->assertEquals('sendMessage', $telegramMessageDto->getMethod());
    }

    public function testGetChatId(): void
    {
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setChatId(9223372036854775807);
        $this->assertEquals(9223372036854775807, $telegramMessageDto->getChatId());
    }

    public function testGetText(): void
    {
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setText('/mode');
        $this->assertEquals('/mode', $telegramMessageDto->getText());
    }

    public function testToArray(): void
    {

        $params = [
            'method' => 'sendMessage',
            'chat_id' => 9223372036854775807,
            'text' => '/mode'
        ];

        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setMethod('sendMessage')
            ->setChatId(9223372036854775807)
            ->setText('/mode');

        $this->assertEquals($params, $telegramMessageDto->toArray());
    }
}
