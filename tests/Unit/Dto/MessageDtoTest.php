<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ChatDto;
use App\Dto\MessageDto;
use App\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class MessageDtoTest extends TestCase
{
    public function testId(): void
    {
        $messageDto = new MessageDto();
        $messageDto->setMessageId(2239818);
        $this->assertEquals(2239818, $messageDto->getMessageId());
    }

    public function testFrom(): void
    {
        $messageDto = new MessageDto();
        $userDto = new UserDto();
        $messageDto->setFrom($userDto);
        $this->assertInstanceOf(UserDto::class, $messageDto->getFrom());
    }

    public function testDate(): void
    {
        $messageDto = new MessageDto();
        $messageDto->setDate(1686165587);
        $this->assertEquals(1686165587, $messageDto->getDate());
    }

    public function testChat(): void
    {
        $messageDto = new MessageDto();
        $chatDto = new ChatDto();
        $messageDto->setChat($chatDto);
        $this->assertInstanceOf(ChatDto::class, $messageDto->getChat());
    }

    public function testText(): void
    {
        $messageDto = new MessageDto();
        $messageDto->setText('/start');
        $this->assertEquals('/start', $messageDto->getText());
    }
}
