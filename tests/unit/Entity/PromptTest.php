<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Prompt;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PromptTest extends TestCase
{
    public function testGetId(): void
    {
        $prompt = new Prompt();
        $prompt->setId(1);
        $this->assertEquals(1, $prompt->getId());
    }

    public function testGetLanguage(): void
    {
        $prompt = new Prompt();
        $prompt->setLanguage('es');

        $this->assertEquals('es', $prompt->getLanguage());
    }


    public function testGetMessage(): void
    {
        $prompt = new Prompt();
        $prompt->setMessage("hello world");

        $this->assertEquals('hello world', $prompt->getMessage());
    }

    public function testGetCreatedAt(): void
    {
        $prompt = new Prompt();
        $date = new DateTimeImmutable();
        $prompt->setCreatedAt($date);
        $this->assertEquals($date, $prompt->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {

        $prompt = new Prompt();
        $date = new DateTimeImmutable();
        $prompt->setCreatedAt($date);

        $this->assertEquals($date, $prompt->getCreatedAt());
    }

    public function testUpdatedTimestamps(): void
    {

        $prompt = new Prompt();
        $prompt->updatedTimestamps();

        $this->assertInstanceOf(DateTimeImmutable::class, $prompt->getUpdatedAt());
    }

    public function testGetRole(): void
    {
        $prompt = new Prompt();
        $prompt->setRole('doctor');

        $this->assertEquals('doctor', $prompt->getRole());
    }
}
