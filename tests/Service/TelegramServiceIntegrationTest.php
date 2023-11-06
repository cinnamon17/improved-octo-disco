<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TelegramServiceIntegrationTest extends WebTestCase
{
    public function testSendMessage(): void
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

        $client = static::createClient();
        $crawler = $client->request('POST', '/telegram', content: $fakeUpdate);

        $this->assertResponseIsSuccessful();

    }
}
