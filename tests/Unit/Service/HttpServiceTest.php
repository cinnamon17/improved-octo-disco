<?php

namespace App\Tests\Unit\Service;

use App\Dto\ChatPromptMessageDto;
use App\Dto\TelegramMessageDto;
use App\Service\HttpService;
use App\Service\TelegramDtoFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

class HttpServiceTest extends TestCase
{
    private ContainerBagInterface $env;
    private string $expected;
    private JsonMockResponse $response;
    private MockHttpClient $client;
    private HttpService $http;
    private TelegramDtoFactory $dtoFactory;

    protected function setUp(): void
    {
        $this->env = $this->createStub(ContainerBagInterface::class);
        $this->dtoFactory = $this->createStub(TelegramDtoFactory::class);
        $this->env->method('get')
            ->willReturn('completions');

        $this->expected = '{
        "id": "chatcmpl-123",
        "object": "chat.completion",
        "created": 1677652288,
        "model": "gpt-3.5-turbo-0613",
        "choices": [{
            "index": 0,
            "message": {
                "role": "assistant",
                "content": "\n\nHello there, how may I assist you today?"
            },
        "finish_reason": "stop"
        }],
        "usage": {
            "prompt_tokens": 9,
            "completion_tokens": 12,
            "total_tokens": 21
        }
    }';
        $this->response = new JsonMockResponse(json_decode($this->expected));
        $this->client = new MockHttpClient($this->response, 'https://api.openai.com/v1/chat/completions');
        $this->http = new HttpService($this->client, $this->env, $this->dtoFactory);
    }

    public function testChatCompletionReturnAnArrayWithOpenApiResponse(): void
    {

        $chatPromptMessageDto = new ChatPromptMessageDto();
        $chatPromptMessageDto
            ->setPrompt('test')
            ->setMessage('hola');

        $openapiArrayResponse = $this->http->chatCompletion($chatPromptMessageDto);
        $expectedArray = json_decode($this->expected, true);
        $this->assertSame($expectedArray, $openapiArrayResponse->toArray());
    }

    public function testChatCompletionAssertOpenApiUrl(): void
    {

        $chatPromptMessageDto = new ChatPromptMessageDto();
        $chatPromptMessageDto
            ->setPrompt('test')
            ->setMessage('hola');

        $this->http->chatCompletion($chatPromptMessageDto);

        $openApiUrl = $this->response->getRequestUrl();
        $this->assertSame('https://api.openai.com/v1/chat/completions', $openApiUrl);
    }

    public function testRequest(): void
    {

	$telegramMessageDto = new TelegramMessageDto();
	$telegramMessageDto
	    ->setMethod('sendMessage')
	    ->setChatId(11111111)
	    ->setText('test')
	;

        $request = $this->http->telegramRequest($telegramMessageDto);

        $this->assertIsArray($request);
    }
}
