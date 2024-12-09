<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ChatDto;
use PHPUnit\Framework\TestCase;

class ChatDtoTest extends TestCase
{
    public function testGetIdMaxBigInteger(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setId(9223372036854775807);
        $this->assertEquals(9223372036854775807, $chatDto->getId());
    }

    public function testGetType(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setType('private');
        $this->assertEquals('private', $chatDto->getType());
    }

    public function testGetTitle(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setTitle('title group');
        $this->assertEquals('title group', $chatDto->getTitle());
    }

    public function testGetUsername(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setUsername('cinnamon');
        $this->assertEquals('cinnamon', $chatDto->getUsername());
    }

    public function testGetFirstName(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setFirstName('coolname');
        $this->assertEquals('coolname', $chatDto->getFirstName());
    }

    public function testGetLastName(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setLastName('lastname');
        $this->assertEquals('lastname', $chatDto->getLastName());
    }

    public function testGetIsForum(): void
    {
        $chatDto = new ChatDto();
        $chatDto->setIsForum(false);
        $this->assertEquals(false, $chatDto->getIsForum());
    }
}
