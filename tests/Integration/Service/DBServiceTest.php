<?php

namespace App\Tests\Integration\Service;

use App\Entity\Prompt;
use App\Repository\PromptRepository;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\RequestSerializer;
use App\Service\TelegramBotUpdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class DBServiceTest extends KernelTestCase
{

    public function testInsertUserInDB(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $request = $this->createStub(Request::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $requestStack = $this->createStub(RequestStack::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(9223372036854775807);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $jsonRequest = '{
            "update_id": 829824026,
            "message": {
            "message_id": 2239818,
            "from": {
                "id": 1136298813,
                "is_bot": false,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "language_code": "es"
            },
            "chat": {
                "id": 1136298813,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "type": "private"
            },
                "date": 1686165587,
                "text": "/mode"
            }
        }';

        $request->method('getContent')
            ->willReturn($jsonRequest);

        $requestStack->method('getCurrentRequest')
            ->willReturn($request);

        $serializer = $container->get(SerializerInterface::class);

        $requestSerializer = new RequestSerializer($serializer, $requestStack);
        $container->set(RequestSerializer::class, $requestSerializer);
        $container->set(TelegramBotUpdate::class, $telegramBotUpdate);
        $dbService = static::getContainer()->get(DBService::class);
        $dbService->insertUserInDb();

        $user = $dbService->userFindOneBy(9223372036854775807);

        $this->assertEquals(9223372036854775807, $user->getChatId());
    }

    public function testGetPrompt(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $request = $this->createStub(Request::class);
        $requestStack = $this->createStub(RequestStack::class);

        $prompt = new Prompt();
        $prompt->setLanguage('es');
        $prompt->setRole('asistente');
        $prompt->setMessage('hello');

        $jsonRequest = '{
            "update_id": 829824026,
            "message": {
            "message_id": 2239818,
            "from": {
                "id": 1136298813,
                "is_bot": false,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "language_code": "es"
            },
            "chat": {
                "id": 1136298813,
                "first_name": "Nelson",
                "last_name": "Moncada",
                "username": "juniormoncada17",
                "type": "private"
            },
                "date": 1686165587,
                "text": "/mode"
            }
        }';

        $entityManager->persist($prompt);
        $entityManager->flush();

        $telegramBotUpdate->method('getChatId')
            ->willReturn(9223372036854775807);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $request->method('getContent')
            ->willReturn($jsonRequest);

        $requestStack->method('getCurrentRequest')
            ->willReturn($request);

        $serializer = $container->get(SerializerInterface::class);

        $requestSerializer = new RequestSerializer($serializer, $requestStack);
        $container->set(RequestSerializer::class, $requestSerializer);
        $container->set(TelegramBotUpdate::class, $telegramBotUpdate);

        $dbService = static::getContainer()->get(DBService::class);
        $dbService->insertUserInDb();

        $this->assertInstanceOf(Prompt::class, $dbService->getPrompt());
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
