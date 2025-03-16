<?php

namespace App\Tests\Unit\Dto;

use App\Dto\TelegramActionDto;
use PHPUnit\Framework\TestCase;

class TelegramActionDtoTest extends TestCase
{
    public function testGetMethod(): void
    {
        $telegramActionDto = new TelegramActionDto();
        $telegramActionDto->setMethod('sendChatAction');
        $this->assertEquals('sendChatAction', $telegramActionDto->getMethod());
    }

    public function testGetChatId(): void
    {
        $telegramActionDto = new TelegramActionDto();
        $telegramActionDto->setChatId(9223372036854775807);
        $this->assertEquals(9223372036854775807, $telegramActionDto->getChatId());
    }

    public function testGetAction(): void
    {
        $telegramActionDto = new TelegramActionDto();
        $telegramActionDto->setAction('typing');
        $this->assertEquals('typing', $telegramActionDto->getAction());
    }

    public function testToArray(): void
    {

        $params = [
            'method' => 'sendChatAction',
            'chat_id' => 9223372036854775807,
            'action' => 'typing'
        ];

        $telegramActionDto = new TelegramActionDto();
        $telegramActionDto->setMethod('sendChatAction')
            ->setChatId(9223372036854775807)
            ->setAction('typing');

        $this->assertEquals($params, $telegramActionDto->toArray());
    }
}
