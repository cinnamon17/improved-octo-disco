<?php

namespace App\Tests\Unit\Dto;

use App\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    public function testIdMaxBigInteger(): void
    {
        $userDto = new UserDto();
        $userDto->setId(9223372036854775807);
        $this->assertEquals(9223372036854775807, $userDto->getId());
    }

    public function testIsBot(): void
    {
        $userDto = new UserDto();
        $userDto->setIsBot(false);
        $this->assertEquals(false, $userDto->getIsBot());
    }

    public function testFirstName(): void
    {
        $userDto = new UserDto();
        $userDto->setFirstName('coolname');
        $this->assertEquals('coolname', $userDto->getFirstName());
    }

    public function testLastName(): void
    {
        $userDto = new UserDto();
        $userDto->setLastName('lastname');
        $this->assertEquals('lastname', $userDto->getLastName());
    }

    public function testUsername(): void
    {
        $userDto = new UserDto();
        $userDto->setUsername('cinnamon');
        $this->assertEquals('cinnamon', $userDto->getUsername());
    }

    public function testLanguageCode(): void
    {
        $userDto = new UserDto();
        $userDto->setLanguageCode('es');
        $this->assertEquals('es', $userDto->getLanguageCode());
    }

    public function testIsPremium(): void
    {
        $userDto = new UserDto();
        $userDto->setIsPremium(null);
        $this->assertEquals(null, $userDto->getIsPremium());
    }

    public function testAddedToAttachmentMenu(): void
    {
        $userDto = new UserDto();
        $userDto->setAddedToAttachmentMenu(true);
        $this->assertEquals(true, $userDto->getAddedToAttachmentMenu());
    }

    public function testCanJoinGroups(): void
    {
        $userDto = new UserDto();
        $userDto->setCanJoinGroups(true);
        $this->assertEquals(true, $userDto->getCanJoinGroups());
    }
}
