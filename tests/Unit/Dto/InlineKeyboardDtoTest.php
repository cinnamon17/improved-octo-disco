<?php

namespace App\Tests\Unit\Dto;

use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use PHPUnit\Framework\TestCase;

class InlineKeyboardDtoTest extends TestCase
{
    public function testGetInlineKeyboard(): void
    {

        $translatorButton = new InlineKeyboardButtonDto();
        $translatorButton
            ->setText('translator')
            ->setData('translator');

        $assistantButton = new InlineKeyboardButtonDto();
        $assistantButton
            ->setText('assistant')
            ->setData('assistant');

        $inlineKeyboardButtonRowDto = new InlineKeyboardButtonRowDto();
        $inlineKeyboardButtonRowDto
            ->add($translatorButton)
            ->add($assistantButton);

        $inlineKeyboardButtonRowDto2 = new InlineKeyboardButtonRowDto();
        $inlineKeyboardButtonRowDto
            ->add($translatorButton)
            ->add($assistantButton);

        $array = [
            'inline_keyboard' => [
                $inlineKeyboardButtonRowDto->getButtons(),
                $inlineKeyboardButtonRowDto2->getButtons()
            ]
        ];

        $inlineKeyboardDto = new InlineKeyboardDto();
        $inlineKeyboardDto
            ->add($inlineKeyboardButtonRowDto)
            ->add($inlineKeyboardButtonRowDto2);

        $this->assertEquals($array, $inlineKeyboardDto->getKeyboard());
    }
}
