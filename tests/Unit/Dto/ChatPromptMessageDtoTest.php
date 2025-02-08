<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ChatPromptMessageDto;
use PHPUnit\Framework\TestCase;

class ChatPromptMessageDtoTest extends TestCase
{
    public function testgetMessage(): void
    {
        $messagePromptDto = new ChatPromptMessageDto();
        $messagePromptDto->setMessage('message');
        $this->assertEquals('message', $messagePromptDto->getMessage());
    }

    public function testgetPrompt(): void
    {
        $messagePromptDto = new ChatPromptMessageDto();
        $messagePromptDto->setPrompt('testPrompt');
        $this->assertEquals('testPrompt', $messagePromptDto->getPrompt());
    }
}
