<?php

namespace App\Tests\Unit\Service;

use App\Entity\Prompt;
use App\Entity\User;
use App\Repository\PromptRepository;
use App\Repository\UserRepository;
use App\Service\BotUpdateTranslator;
use App\Service\DBService;
use App\Service\TelegramBotUpdate;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DBServiceTest extends TestCase
{

    private EntityManagerInterface $em;
    private PromptRepository $promptRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->promptRepository = $this->createStub(PromptRepository::class);
        $this->userRepository = $this->createStub(UserRepository::class);
    }
    public function testUserFindOneBy(): void
    {


        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);
        $user = $dbService->userFindOneBy('1234');

        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserFindOneByNull(): void
    {

        $this->userRepository->method('findOneBy')
            ->willReturn(null);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);
        $user = $dbService->userFindOneBy('1234');

        $this->assertNull($user);
    }

    public function testPromptFindOneBy(): void
    {

        $this->promptRepository->method('findOneBy')
            ->willReturn(new Prompt());

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);
        $prompt = $dbService->promptFindOneBy('doctor', 'en');

        $this->assertInstanceOf(Prompt::class, $prompt);
    }

    public function testGetPrompt(): void
    {

        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $user = new User();
        $user->setMode('doctor');
        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $telegramBotUpdate->method('getLanguageCode')
            ->willReturn('es');

        $this->promptRepository->method('findOneBy')
            ->willReturn(new Prompt());

        $this->userRepository->method('findOneBy')
            ->willReturn($user);

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);

        $prompt = $dbService->getPrompt($bt);
        $this->assertInstanceOf(Prompt::class, $prompt);
    }

    public function testIsUserExists(): void
    {


        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);
        $this->assertTrue($dbService->isUserExists($bt));
    }

    public function testIsUserNoExists(): void
    {

        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(null);

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository);
        $this->assertFalse($dbService->isUserExists($bt));
    }

    public function testInsertUserInDb(): void
    {
        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $bt->method('getAssistantMessage')
            ->willReturn('Hello welcome');

        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())
            ->method('flush');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->insertUserInDb($bt);
    }

    public function testUpdateUserInDb(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('nameTest');

        $telegramBotUpdate->method('getLastName')
            ->willReturn('Last');

        $telegramBotUpdate->method('getUserName')
            ->willReturn('username');

        $telegramBotUpdate->method('getMessageText')
            ->willReturn('text message');

        $telegramBotUpdate->method('getMessageId')
            ->willReturn((float) 12345);

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $em->expects($this->once())
            ->method('flush');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->updateUserInDb($bt);
    }

    public function testSetBotMode(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $bt = $this->createStub(BotUpdateTranslator::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn((float) 12345);

        $telegramBotUpdate->method('getCallbackQuery')
            ->with('data')
            ->willReturn((float) 12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $bt->method('update')
            ->willReturn($telegramBotUpdate);

        $em->expects($this->once())
            ->method('flush');


        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->setBotMode($bt);
    }

    public function testPersist(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->persist(new User());
    }

    public function testFlush(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('flush');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->flush(new User());
    }

    public function testSave(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('flush');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository);
        $dbService->save(new User());
    }
}
