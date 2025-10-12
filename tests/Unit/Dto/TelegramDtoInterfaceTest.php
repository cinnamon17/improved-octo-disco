<?php

namespace App\Tests\Unit\Dto;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramDtoInterface;
use App\Dto\TelegramMessageDto;
use PHPUnit\Framework\TestCase;

class TelegramDtoInterfaceTest extends TestCase{

    public function testAllTelegramDtosImplementCommonInterface(): void
    {
	$dtos = [
	    new TelegramMessageDto(),
	    new TelegramActionDto(), 
	    new AnswerCallbackQueryDto(),
	];

	foreach($dtos as $dto){
	    $this->assertInstanceOf(TelegramDtoInterface::class, $dto);
	}
    }


}
