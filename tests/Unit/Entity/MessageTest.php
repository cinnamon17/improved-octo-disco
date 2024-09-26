<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testGetMessage(): void
    {
        $message = new Message();
        $message->setId(1);
        $this->assertEquals(1, $message->getId());
    }


    public function testGetMessageId(): void
    {

        $message = new Message();
        $message->setMessageId(10);
        $this->assertEquals(10, $message->getMessageId());
    }

    public function testGetUser(): void
    {
        $user = new User();
        $message = new Message();
        $message->setUser($user);
        $message->getUser();

        $this->assertInstanceOf(User::class, $message->getUser());
    }


    public function testGetText(): void
    {
        $message = new Message();
        $message->setText("hello");
        $this->assertEquals("hello", $message->getText());
    }

    public function testGetUpdateAt(): void
    {
        $message = new Message();
        $message->setUpdatedAt(new DateTimeImmutable());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }
}
