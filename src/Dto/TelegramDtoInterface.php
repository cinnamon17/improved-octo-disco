<?php

namespace App\Dto;

interface TelegramDtoInterface
{
    public function getMethod(): String;
    public function toArray(): array;
}
