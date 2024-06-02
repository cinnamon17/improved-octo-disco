<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class BookControllerTest extends ApiTestCase
{
    public function testSomething(): void
    {
        $response = static::createClient()->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/api/books']);
    }
}
