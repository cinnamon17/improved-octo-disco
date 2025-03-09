<?php

namespace App\Dto;

class InlineKeyboardButtonRowDto
{

    private array $buttons = [];

    public function add(InlineKeyboardButtonDto $button): self
    {
        array_push($this->buttons, $button->toArray());
        return $this;
    }

    public function getButtons()
    {
        return $this->buttons;
    }
}
