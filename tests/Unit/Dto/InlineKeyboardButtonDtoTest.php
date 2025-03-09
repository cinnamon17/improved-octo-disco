<?php

namespace App\Tests\Unit\Dto;

use App\Dto\InlineKeyboardButtonDto;
use PHPUnit\Framework\TestCase;

class InlineKeyboardButtonDtoTest extends TestCase
{
    public function testText(): void
    {
        $inlineKeyboardButtonDto = new InlineKeyboardButtonDto();
        $inlineKeyboardButtonDto
            ->setText('chef')
            ->setData('test');

        $this->assertEquals('chef', $inlineKeyboardButtonDto->getText());
    }

    public function testCallbackData(): void
    {
        $inlineKeyboardButtonDto = new InlineKeyboardButtonDto();
        $inlineKeyboardButtonDto
            ->setText('chef')
            ->setData('test');

        $this->assertEquals('test', $inlineKeyboardButtonDto->getData());
    }

    public function testToArray()
    {
        $inlineKeyboardButtonDto = new InlineKeyboardButtonDto();
        $inlineKeyboardButtonDto
            ->setText('chef')
            ->setData('test');

        $array = ['text' => 'chef', 'callback_data' => 'test'];

        $this->assertEquals($array, $inlineKeyboardButtonDto->toArray());
    }
}
