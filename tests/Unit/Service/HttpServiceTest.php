<?php

namespace App\Tests\Unit\Service;

use App\Service\HttpService;
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

    protected function setUp(): void
    {
        $this->env = $this->createStub(ContainerBagInterface::class);
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
        $this->client = new MockHttpClient($this->response, 'https://example.com');
        $this->http = new HttpService($this->client, $this->env);
    }

    public function testChatCompletionReturnAnArrayWithOpenApiResponse(): void
    {

        $openapiArrayResponse = $this->http->chatCompletion('hola', 'test');
        $expectedArray = json_decode($this->expected, true);

        $this->assertSame($expectedArray, $openapiArrayResponse);
    }

    public function testChatCompletionAssertOpenApiURL(): void
    {

        $this->http->chatCompletion('hola', 'test');

        $openApiUrl = $this->response->getRequestUrl();
        $this->assertSame('https://api.openai.com/v1/chat/completions', $openApiUrl);
    }

    public function testRequest(): void
    {

        $request = $this->http->request(["hello" => "world"]);

        $this->assertIsArray($request);
    }

    public function testChatCompletionAssertRequiredRequestOptions()
    {

        $this->http->chatCompletion('hola', 'test');
        $requiredOptions = '{"model":"gpt-3.5-turbo","messages":[{"role":"system","content":"test"},{"role":"user","content":"hola"}]}';
        $actualPostedOptions = $this->response->getRequestOptions()['body'];
        $this->assertSame($requiredOptions, $actualPostedOptions);
    }
}
