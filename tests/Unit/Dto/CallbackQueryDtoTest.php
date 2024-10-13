<?php

namespace App\Tests\Unit\Dto;

use App\Dto\UserDto;
use App\Dto\CallbackQueryDto;
use PHPUnit\Framework\TestCase;

class CallbackQueryDtoTest extends TestCase
{
    public function testId(): void
    {
        $callbackQueryDto = new CallbackQueryDto();
        $callbackQueryDto->setId('2239818');
        $this->assertEquals('2239818', $callbackQueryDto->getId());
    }

    public function testFrom(): void
    {
        $callbackQueryDto = new CallbackQueryDto();
        $userDto = new UserDto();
        $callbackQueryDto->setFrom($userDto);
        $this->assertInstanceOf(UserDto::class, $callbackQueryDto->getFrom());
    }

    public function testData(): void
    {
        $callbackQueryDto = new CallbackQueryDto();
        $callbackQueryDto->setData('doctor');
        $this->assertEquals('doctor', $callbackQueryDto->getData());
    }

    public function testInlineMessageId(): void
    {
        $callbackQueryDto = new CallbackQueryDto();
        $callbackQueryDto->setInlineMessageId('1234csdbsk4839');
        $this->assertEquals('1234csdbsk4839', $callbackQueryDto->getInlineMessageId());
    }
}
