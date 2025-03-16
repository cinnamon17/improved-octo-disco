<?php

namespace App\Dto;

class InlineKeyboardDto
{

    private array $rows = [];

    public function add(InlineKeyboardButtonRowDto $row): self
    {
        array_push($this->rows, $row->getButtons());
        return $this;
    }

    public function getKeyboard(): array
    {
        $keyboard = ['inline_keyboard' => $this->rows];
        return $keyboard;
    }
}
