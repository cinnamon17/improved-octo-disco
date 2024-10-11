<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId(): void
    {
        $user = new User();
        $user->setId(1);
        $this->assertEquals(1, $user->getId());
    }

    public function testGetMessage(): void
    {
        $message = new Message();
        $user = new User();
        $user->addMessage($message);
        $this->assertInstanceOf(Collection::class, $user->getMessage());
    }


    public function testRemoveMessage(): void
    {

        $message = new Message();
        $user = new User();
        $user->addMessage($message);
        $user->removeMessage($message);
        $this->assertEmpty($user->getMessage());
    }

    public function testGetChatId(): void
    {

        $user = new User();
        $user->setChatId(10);
        $this->assertEquals(10, $user->getChatId());
    }

    public function testGetLastname(): void
    {
        $user = new User();
        $user->setLastName("testName");
        $this->assertEquals("testName", $user->getLastName());
    }


    public function testGetFistName(): void
    {
        $user = new User();
        $user->setFirstName("testFirstName");
        $this->assertEquals("testFirstName", $user->getFirstName());
    }

    public function testGetUsername(): void
    {

        $user = new User();
        $user->setUsername("testUsername");
        $this->assertEquals("testUsername", $user->getUsername());
    }

    public function testGetCreatedAt(): void
    {
        $user = new User();
        $user->setCreatedAt(new DateTimeImmutable());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $user = new User();
        $user->setUpdatedAt(new DateTimeImmutable());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testUpdatedTimestamps(): void
    {
        $user = new User();
        $user->updatedTimestamps();
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testGetMode(): void
    {

        $user = new User();
        $user->setMode("testMode");
        $this->assertEquals("testMode", $user->getMode());
    }

    public function testisIsbot(): void
    {
        $user = new User();
        $user->setIsBot(true);
        $this->assertTrue($user->isIsBot());
    }

    public function testGetisbot(): void
    {
        $user = new User();
        $user->setIsBot(true);
        $this->assertTrue($user->getIsBot());
    }
}
