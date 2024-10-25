<?php

namespace App\Tests\Unit\Dto;

use App\Dto\OpenAIJsonDto;
use App\Dto\OpenAIMessageDto;
use PHPUnit\Framework\TestCase;

class OpenAIJsonDtoTest extends TestCase
{
    public function testGetModel(): void
    {

        $openAIJsonDto = new OpenAIJsonDto();
        $openAIJsonDto->setModel('gpt-3.5-turbo');
        $this->assertEquals('gpt-3.5-turbo', $openAIJsonDto->getModel());
    }

    public function testGetMessage(): void
    {
        $openAIJsonDto = new OpenAIJsonDto();

        $messages = [
            new OpenAIMessageDto('system', 'prompt'),
            new OpenAIMessageDTO('user', 'message'),
        ];

        $openAIJsonDto->setMessages($messages);
        $this->assertEquals($messages, $openAIJsonDto->getMessages());
    }

    public function testSetMessage(): void
    {
        $openAIJsonDto = new OpenAIJsonDto();

        $messages = [
            new OpenAIMessageDto('system', 'prompt'),
            new OpenAIMessageDTO('user', 'message'),
        ];

        $openAIJsonDto->setMessages($messages);
        $this->assertIsArray($openAIJsonDto->getMessages());
    }
}
