<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\PromptRepository;
use App\Repository\UserRepository;
use App\Service\DBService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DBServiceTest extends TestCase
{
    public function testUserFindOneBy(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $promptRepository = $this->createStub(PromptRepository::class);
        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findOneBy')
            ->willReturn(new User());

        $dbService = new DBService($em, $userRepository, $promptRepository);
        $user = $dbService->userFindOneBy('1234');

        $this->assertInstanceOf(User::class, $user);
    }
}
