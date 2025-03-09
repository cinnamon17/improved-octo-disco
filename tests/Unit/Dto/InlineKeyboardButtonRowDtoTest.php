<?php

namespace App\Tests\Unit\Dto;

use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use PHPUnit\Framework\TestCase;

class InlineKeyboardButtonRowDtoTest extends TestCase
{
    public function testgetButtons(): void
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

        $array = [
            $translatorButton->toArray(),
            $assistantButton->toArray(),
        ];

        $this->assertEquals($array, $inlineKeyboardButtonRowDto->getButtons());
    }
}
