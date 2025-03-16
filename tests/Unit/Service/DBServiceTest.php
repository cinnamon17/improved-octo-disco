<?php

namespace App\Tests\Unit\Service;

use App\Entity\Message;
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
    private BotUpdateTranslator $bt;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->promptRepository = $this->createStub(PromptRepository::class);
        $this->userRepository = $this->createStub(UserRepository::class);
        $this->bt = $this->createStub(BotUpdateTranslator::class);
    }

    public function testGetPrompt(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $user = new User();
        $user->setMode('doctor');
        $user->setChatId(12345);
        $telegramBotUpdate->method('getChatId')
            ->willReturn(12345);

        $telegramBotUpdate->method('getLocale')
            ->willReturn('es');

        $this->promptRepository->method('findOneBy')
            ->willReturn(new Prompt());

        $this->userRepository->method('findOneBy')
            ->willReturn($user);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);

        $prompt = $dbService->getPrompt($user, 'es');
        $this->assertInstanceOf(Prompt::class, $prompt);
    }

    public function testIsUserExists(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);
        $this->assertTrue($dbService->isUserExists(12345));
    }

    public function testIsUserNoExists(): void
    {

        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(null);

        $dbService = new DBService($this->em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);
        $this->assertFalse($dbService->isUserExists(1234));
    }

    public function testInsertUserInDb(): void
    {
        $user = $this->createStub(User::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(12345);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('Test');

        $telegramBotUpdate->method('getIsBot')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())
            ->method('flush');

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);
        $dbService->insertUserInDb($user);
    }

    public function testUpdateUserInDb(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getChatId')
            ->willReturn(12345);

        $telegramBotUpdate->method('getFirstName')
            ->willReturn('nameTest');

        $telegramBotUpdate->method('getLastName')
            ->willReturn('Last');

        $telegramBotUpdate->method('getUserName')
            ->willReturn('username');

        $telegramBotUpdate->method('getMessageText')
            ->willReturn('text message');

        $telegramBotUpdate->method('getMessageId')
            ->willReturn(12345);

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $em->expects($this->once())
            ->method('flush');

        $user = (new User())
            ->setChatId(12345)
            ->setFirstName('nameTest')
            ->setMode('asistente')
            ->setIsBot(false);

        $message = (new Message())
            ->setText('test')
            ->setMessageId(12345);

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);
        $dbService->updateUserInDb($user, $message);
    }

    public function testUpdateUserMode(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $user = $this->createStub(User::class);
        $telegramBotUpdate = $this->createStub(TelegramBotUpdate::class);

        $telegramBotUpdate->method('getCallbackQueryChatId')
            ->willReturn(12345);

        $telegramBotUpdate->method('getCallbackQueryData')
            ->willReturn('12345');

        $this->userRepository->method('findOneBy')
            ->willReturn(new User());

        $em->expects($this->once())
            ->method('flush');

        $user
            ->method('getMode')
            ->willReturn('asistente');

        $user
            ->method('getChatId')
            ->willReturn(1234);

        $dbService = new DBService($em, $this->userRepository, $this->promptRepository, $telegramBotUpdate, $this->bt);
        $dbService->UpdateUserMode($user);
    }
}
