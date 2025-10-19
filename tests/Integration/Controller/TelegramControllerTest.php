<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelegramControllerTest extends WebTestCase
{
    private function assertIsTelegramResponse(Response $response, string $expectedBody): void
    {
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $expected = json_decode($expectedBody, true);
        $actual = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('ok', $actual);
        $this->assertTrue($actual['ok']);

        $this->assertArrayHasKey('result', $actual);
        $this->assertArrayHasKey('text', $actual['result']);
        
        if (isset($expected['result']['text'])) {
            $this->assertSame($expected['result']['text'], $actual['result']['text']);
        }
    }

    public function testMode(): void
    {
        $fakeUpdate = '{
            "update_id": 829824026,
            "message": {
                "message_id": 2239818,
                "from": {
                    "id": 1136298813, "is_bot": false, "first_name": "Nelson", "last_name": "Moncada", "username": "juniormoncada17", "language_code": "es"
                },
                "chat": {
                    "id": 1136298813, "first_name": "Nelson", "last_name": "Moncada", "username": "juniormoncada17", "type": "private"
                },
                "date": 1686165587,
                "text": "/mode"
            }
        }';

        $expectedResponse = '{
            "ok": true,
            "result": {
                "text": "¿Que modo te gustaria que interpretara? 🎭",
                "reply_markup": {}
            }
        }';
        
        $client = static::createClient();
        $client->request('POST', '/telegram', content: $fakeUpdate);

        $this->assertIsTelegramResponse($client->getResponse(), $expectedResponse);
    }

    public function testStart(): void
    {
        $fakeUpdate = '{
            "update_id": 829824026,
            "message": {
                "message_id": 2239818,
                "from": {
                    "id": 1136298813, "is_bot": false, "first_name": "Nelson", "last_name": "Moncada", "username": "juniormoncada17", "language_code": "es"
                },
                "chat": {
                    "id": 1136298813, "first_name": "Nelson", "last_name": "Moncada", "username": "juniormoncada17", "type": "private"
                },
                "date": 1686165587,
                "text": "/start"
            }
        }';

        $client = static::createClient();
        $client->request('POST', '/telegram', content: $fakeUpdate);
        
        $response = $client->getResponse();
        
        $this->assertResponseIsSuccessful();
        
        $content = $response->getContent();
        
        $this->assertNotNull($content); 
        $data = json_decode($content, true);

        $this->assertArrayHasKey('ok', $data);
	$this->assertEquals(true, $data['ok']);
    }
}
