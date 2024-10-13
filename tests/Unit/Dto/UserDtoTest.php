<?php

namespace App\Tests\Unit\Dto;

use App\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    public function testIdMaxBigInteger(): void
    {
        $chatDto = new UserDto();
        $chatDto->setId(9223372036854775807);
        $this->assertEquals(9223372036854775807, $chatDto->getId());
    }

    public function testIsBot(): void
    {
        $chatDto = new UserDto();
        $chatDto->setIsBot(false);
        $this->assertEquals(false, $chatDto->getIsBot());
    }

    public function testFirstName(): void
    {
        $chatDto = new UserDto();
        $chatDto->setFirstName('coolname');
        $this->assertEquals('coolname', $chatDto->getFirstName());
    }

    public function testLastName(): void
    {
        $chatDto = new UserDto();
        $chatDto->setLastName('lastname');
        $this->assertEquals('lastname', $chatDto->getLastName());
    }

    public function testUsername(): void
    {
        $chatDto = new UserDto();
        $chatDto->setUsername('cinnamon');
        $this->assertEquals('cinnamon', $chatDto->getUsername());
    }

    public function testLanguageCode(): void
    {
        $chatDto = new UserDto();
        $chatDto->setLanguageCode('es');
        $this->assertEquals('es', $chatDto->getLanguageCode());
    }

    public function testIsPremium(): void
    {
        $chatDto = new UserDto();
        $chatDto->setIsPremium(null);
        $this->assertEquals(null, $chatDto->getIsPremium());
    }

    public function testAddedToAttachmentMenu(): void
    {
        $chatDto = new UserDto();
        $chatDto->setAddedToAttachmentMenu(true);
        $this->assertEquals(true, $chatDto->getAddedToAttachmentMenu());
    }

    public function testCanJoinGroups(): void
    {
        $chatDto = new UserDto();
        $chatDto->setCanJoinGroups(true);
        $this->assertEquals(true, $chatDto->getCanJoinGroups());
    }
}
