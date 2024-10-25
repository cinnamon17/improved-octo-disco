<?php

namespace App\Tests\Unit\Dto;

use App\Dto\HeadersDto;
use App\Dto\OpenAIJsonDto;
use App\Dto\OpenAIMessageDto;
use PHPUnit\Framework\TestCase;

class OpenAIDtoTest extends TestCase
{
    public function testGetHeaders(): void
    {

        $headersDto = new HeadersDto();
        $headersDto->setAccept('application/json')
            ->setAuthorization('Bearer test1234');

        $messages = [
            new OpenAIMessageDto('system', 'assistant'),
            new OpenAIMessageDTO('user', 'test')
        ];

        $jsonDto = new OpenAIJsonDto();
        $jsonDto->setModel('gpt-3.5-turbo')
            ->setMessages($messages);

        $openAIDto = new OpenAIDto();
        $openAIDto->setHeaders($headersDto);
        $openAIDto->setJson($jsonDto);

        $this->assertInstanceOf(HeadersDto::class, $openAIDto->getHeaders());
    }

    public function testGetJson(): void
    {

        $headersDto = new HeadersDto();
        $headersDto->setAccept('application/json')
            ->setAuthorization('Bearer test1234');

        $messages = [
            new OpenAIMessageDto('system', 'assistant'),
            new OpenAIMessageDTO('user', 'test')
        ];

        $jsonDto = new OpenAIJsonDto();
        $jsonDto->setModel('gpt-3.5-turbo')
            ->setMessages($messages);

        $openAIDto = new OpenAIDto();
        $openAIDto->setHeaders($headersDto);
        $openAIDto->setJson($jsonDto);

        $this->assertInstanceOf(OpenAIJsonDto::class, $openAIDto->getJson());
    }
}
