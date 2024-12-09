<?php

namespace App\Tests\Integration\Service;

use App\Entity\Prompt;
use App\Repository\PromptRepository;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use Doctrine\ORM\EntityManagerInterface;
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

    public function testGetPrompt(): void
    {
        self::bootKernel();
        $dbService = static::getContainer()->get(DBService::class);
        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $prompt = new Prompt();
        $prompt->setLanguage('es');
        $prompt->setRole('asistente');
        $prompt->setMessage('hello');

        $entityManager->persist($prompt);
        $entityManager->flush();

        $telegramBotUpdate->method('getChatId')
            ->willReturn(9223372036854775807);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $bt->method('getAssistantMessage')
            ->willReturn('asistente');

        $dbService->insertUserInDb($bt);

        $this->assertInstanceOf(Prompt::class, $dbService->getPrompt($bt));
    }

    public function testGetPromptRepository(): void
    {
        self::bootKernel();
        $promptRepository = static::getContainer()->get(PromptRepository::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);


        $prompt = new Prompt();
        $prompt->setLanguage('es');
        $prompt->setRole('asistente');
        $prompt->setMessage('hello');

        $entityManager->persist($prompt);
        $entityManager->flush();

        $result = $promptRepository->findOneBy(['role' => 'asistente', 'language' => 'es']);
        $this->assertInstanceOf(Prompt::class, $result);
    }
}
