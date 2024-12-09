<?php

namespace App\Tests\Unit\Dto;

use App\Dto\HeadersDto;
use PHPUnit\Framework\TestCase;

class HeadersDtoTest extends TestCase
{
    public function testGetAccept(): void
    {
        $headerDto = new HeadersDto();
        $headerDto->setAccept('application/json');
        $this->assertEquals('application/json', $headerDto->getAccept());
    }

    public function testGetAuthorization(): void
    {
        $headerDto = new HeadersDto();
        $headerDto->setAuthorization('Bearer 1234test');
        $this->assertEquals('Bearer 1234test', $headerDto->getAuthorization());
    }

    public function testToArray(): void
    {
        $headerDto = new HeadersDto();
        $headerDto->setAuthorization('Bearer 1234test')
            ->setAccept('application/json');

        $array = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer 1234test'
        ];

        $this->assertEquals($array, $headerDto->toArray());
    }
}
