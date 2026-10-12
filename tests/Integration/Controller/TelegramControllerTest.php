<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelegramControllerTest extends WebTestCase
{
    /**
     * El token que hay en TELEGRAM_SECRET_TOKEN en el entorno de test. Vacio
     * seria un fallo de configuracion, asi que el helper falla ruidoso en vez
     * de devolver 403 y hidesar el motivo real.
     */
    private function secretToken(): string
    {
        $token = $_ENV['TELEGRAM_SECRET_TOKEN'] ?? $_SERVER['TELEGRAM_SECRET_TOKEN'] ?? '';

        if ($token === '') {
            self::markTestSkipped('TELEGRAM_SECRET_TOKEN vacio en el entorno de test');
        }

        return $token;
    }

    /**
     * Sin secret token, /telegram rechaza antes de deserializar. Sin esto,
     * cualquiera podria acreditar creditos con un successful_payment forjado.
     */
    public function testRejectsRequestWithoutSecretToken(): void
    {
        $client = static::createClient();
        $client->request('POST', '/telegram', content: '{"update_id": 1}');

        $this->assertResponseStatusCodeSame(403);
    }

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
        $client->request('POST', '/telegram', content: $fakeUpdate, server: ['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN' => $this->secretToken()]);

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
        $client->request('POST', '/telegram', content: $fakeUpdate, server: ['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN' => $this->secretToken()]);
        
        $response = $client->getResponse();
        
        $this->assertResponseIsSuccessful();
        
        $content = $response->getContent();
        
        $this->assertNotNull($content); 
        $data = json_decode($content, true);

        $this->assertArrayHasKey('ok', $data);
	$this->assertEquals(true, $data['ok']);
    }
}
