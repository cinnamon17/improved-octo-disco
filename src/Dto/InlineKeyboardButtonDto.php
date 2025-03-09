<?php

namespace App\Dto;

class InlineKeyboardButtonDto
{
    private string $text;
    private string $data;

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toArray()
    {

        $array = [
            'text' => $this->text,
            'callback_data' => $this->data
        ];

        return $array;
    }
}
