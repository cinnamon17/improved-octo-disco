<?php

namespace App\Tests\Unit\Dto;

use App\Dto\OpenAIMessageDto;
use PHPUnit\Framework\TestCase;

class OpenAIMessageDtoTest extends TestCase
{
    public function testGetRole(): void
    {
        $openAIMessageDto = new OpenAIMessageDto();
        $openAIMessageDto->setRole('system');
        $this->assertEquals('system', $openAIMessageDto->getRole());
    }

    public function testGetContent(): void
    {
        $openAIMessageDto = new OpenAIMessageDto();
        $openAIMessageDto->setContent('assistant');
        $this->assertEquals('assistant', $openAIMessageDto->getContent());
    }
}
