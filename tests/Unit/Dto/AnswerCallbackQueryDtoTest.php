<?php

namespace App\Tests\Unit\Dto;

use App\Dto\AnswerCallbackQueryDto;
use PHPUnit\Framework\TestCase;

class AnswerCallbackQueryDtoTest extends TestCase
{
    public function testGetCallbackQueryId(): void
    {
        $answerCallbackQueryDto = new AnswerCallbackQueryDto();
        $answerCallbackQueryDto->setId('2239818');
        $this->assertEquals('2239818', $answerCallbackQueryDto->getId());
    }

    public function testGetMethod(): void
    {
        $answerCallbackQueryDto = new AnswerCallbackQueryDto();
        $answerCallbackQueryDto->setMethod('answerCallbackQuery');
        $this->assertEquals('answerCallbackQuery', $answerCallbackQueryDto->getMethod());
    }

    public function testToArray(): void
    {

        $params = [
            'callback_query_id' => '9223372036854775807',
            'method' => 'answerCallbackQuery'
        ];

        $answerCallbackQueryDto = new AnswerCallbackQueryDto();
        $answerCallbackQueryDto->setMethod('answerCallbackQuery')
            ->setId('9223372036854775807')
            ->setMethod('answerCallbackQuery');

        $this->assertEquals($params, $answerCallbackQueryDto->toArray());
    }
}
