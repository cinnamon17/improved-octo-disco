<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TelegramControllerTest extends WebTestCase
{
    public function testMode(): void
    {
        $fakeUpdate = '{
            "update_id": 829824026,
            "message": {
            "message_id": 2239818,
            "from": {
                "id": 1136298813,
                "is_bot": false,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "language_code": "es"
            },
            "chat": {
                "id": 1136298813,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "type": "private"
            },
                "date": 1686165587,
                "text": "/mode"
            }
        }';


        $expectedResponse = '{
    "ok": true,
    "result": {
        "message_id": 9106,
        "from": {
            "id": 1898926696,
            "is_bot": true,
            "first_name": "ChatGPT | GPT | NelsonBOT",
            "username": "pirela_bot"
        },
        "chat": {
            "id": 1136298813,
            "first_name": "Nelson",
            "last_name": "Moncada",
            "username": "juniormoncada17",
            "type": "private"
        },
        "date": 1699206264,
        "text": "¿Que modo te gustaria que interpretara? 🎭",
        "reply_markup": {
            "inline_keyboard": [
                [
                    {
                        "text": "traductor 🈯",
                        "callback_data": "traductor"
                    },
                    {
                        "text": "asistente 👨🏻‍🏫",
                        "callback_data": "asistente"
                    }
                ],
                [
                    {
                        "text": "chef 🧑🏻‍🍳",
                        "callback_data": "chef"
                    },
                    {
                        "text": "doctor 👨🏻‍⚕️",
                        "callback_data": "doctor"
                    }
                ],
                [
                    {
                        "text": "bussiness.message💡",
                        "callback_data": "startup"
                    }
                ]
            ]
        }
    }
}';

        $client = static::createClient();
        $client->request('POST', '/telegram', content: $fakeUpdate);

        $response = $client->getResponse()->getContent();
        $this->assertEquals(json_decode($expectedResponse), json_decode($response));

    }

    public function testStart(): void
    {
        $fakeUpdate = '{
            "update_id": 829824026,
            "message": {
            "message_id": 2239818,
            "from": {
                "id": 1136298813,
                "is_bot": false,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "language_code": "es"
            },
            "chat": {
                "id": 1136298813,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "type": "private"
            },
                "date": 1686165587,
                "text": "/start"
            }
        }';

        $client = static::createClient();
        $client->request('POST', '/telegram', content: $fakeUpdate);

        $response = $client->getResponse()->getContent();
        $textReceived = json_decode($response)->result->text;
        $this->assertStringContainsString('¡Hola! Soy tu asistente de IA en Telegram', $textReceived);

    }
}
