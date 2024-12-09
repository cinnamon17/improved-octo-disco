<?php

namespace App\Tests\Unit\Dto;

use App\Dto\HeadersDto;
use App\Dto\OpenAIDto;
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

    public function testToArray(): void
    {

        $headersDto = new HeadersDto();
        $headersDto->setAccept('application/json')
            ->setAuthorization('Bearer test1234');

        $system = new OpenAIMessageDto();
        $system->setRole('system')
            ->setContent('assistant');

        $user = new OpenAIMessageDto();
        $user->setRole('user')
            ->setContent('test');

        $jsonDto = new OpenAIJsonDto();
        $jsonDto->setModel('gpt-3.5-turbo')
            ->setMessages([$system, $user]);

        $openAIDto = new OpenAIDto();
        $openAIDto->setHeaders($headersDto);
        $openAIDto->setJson($jsonDto);

        $array = [
            'headers' => $headersDto->toArray(),
            'json' => $jsonDto->toArray()
        ];

        $this->assertEquals($array, $openAIDto->toArray());
    }
}
