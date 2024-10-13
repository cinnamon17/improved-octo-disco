<?php

namespace App\Tests\Unit\Dto;

use App\Dto\CallbackQueryDto;
use App\Dto\MessageDto;
use App\Dto\UpdateDto;
use PHPUnit\Framework\TestCase;

class UpdateDtoTest extends TestCase
{
    public function testId(): void
    {
        $updateDto = new UpdateDto();
        $updateDto->setUpdateId(2239818);
        $this->assertEquals(2239818, $updateDto->getUpdateId());
    }

    public function testMessage(): void
    {
        $updateDto = new UpdateDto();
        $messageDto = new MessageDto();
        $updateDto->setMessage($messageDto);
        $this->assertInstanceOf(MessageDto::class, $updateDto->getMessage());
    }

    public function testCallbackQuery(): void
    {
        $updateDto = new UpdateDto();
        $callbackQuery = new CallbackQueryDto();
        $updateDto->setCallbackQuery($callbackQuery);
        $this->assertInstanceOf(CallbackQueryDto::class, $updateDto->getCallbackQuery());
    }
}
