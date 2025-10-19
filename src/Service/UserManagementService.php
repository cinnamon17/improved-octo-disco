<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class UserManagementService
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
    ) {
        $this->em = $entityManagerInterface;
        $this->userRepository = $userRepository;
    }

    public function findUserByChatId(int $chatId): ?User
    {
        return $this->userRepository->findOneBy(['chat_id' => $chatId]);
    }

    public function registerUser(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function updateUserDetailsAndLogMessage(User $userUpdate, Message $message): void
    {
        $user = $this->userRepository->findOneBy(['chat_id' => $userUpdate->getChatId()]);
        $user->setFirstName($userUpdate->getFirstName())
            ->setLastName($userUpdate->getLastName())
            ->setUsername($userUpdate->getUsername());

        $message = (new Message())
            ->setText($message->getText())
            ->setMessageId($message->getMessageId())
            ->setUser($user);

        $this->em->persist($message);
        $this->em->flush();
    }

    public function updateUserMode(User $userUpdate): void
    {
        $chatId = $userUpdate->getChatId();
        $user = $this->findUserByChatId($chatId);

        if ($user) {
            $user->setMode($userUpdate->getMode());
            $this->em->flush();
        }
    }
    public function handleIncomingUser(User $user, Message $message): void
    {
        $existingUser = $this->findUserByChatId($user->getChatId());

        if (!$existingUser) {
            $this->insertUserInDb($user);
        } else {
            $this->updateUserInDb($existingUser, $message);
        }
    }

    private function insertUserInDb(User $user): void
    {
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdatedAt(new DateTimeImmutable());
        $this->em->persist($user);
	$this->em->flush();
    }

    private function updateUserInDb(User $existingUser, Message $message): void
    {
	$existingUser->setFirstName($existingUser->getFirstName());
	$existingUser->setUpdatedAt(new DateTimeImmutable());

	$message->setUser($existingUser);

	$this->em->persist($message); 
	$this->em->flush();
    }
}
