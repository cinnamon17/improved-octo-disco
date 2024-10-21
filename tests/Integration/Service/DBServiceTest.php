<?php

namespace App\Tests\Integration\Service;

use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DBServiceTest extends KernelTestCase
{

    public function testInsertUserInDB(): void
    {
        self::bootKernel();
        $dbService = static::getContainer()->get(DBService::class);
        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(9223372036854775807);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $bt->method('getAssistantMessage')
            ->willReturn('asistente');

        $dbService->insertUserInDb($bt);

        $user = $dbService->userFindOneBy(9223372036854775807);

        $this->assertEquals(9223372036854775807, $user->getChatId());
    }
}
