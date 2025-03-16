<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\Prompt;
use App\Entity\User;
use App\Repository\PromptRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class DBService
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private PromptRepository $promptRepository;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        PromptRepository $promptRepository,
    ) {
        $this->em = $entityManagerInterface;
        $this->userRepository = $userRepository;
        $this->promptRepository = $promptRepository;
    }

    public function getPrompt(User $user, string $locale): Prompt
    {
        $chatId = $user->getChatId();
        $user = $this->userRepository->findOneBy(['chat_id' => $chatId]);
        return $this->promptRepository->findOneBy(['role' => $user->getMode(), 'language' => $locale]);
    }

    public function isUserExists(int $chatId): bool
    {
        return $this->userRepository->findOneBy(['chat_id' => $chatId]) !== null;
    }

    public function insertUserInDb(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function updateUserInDb(User $userUpdate, Message $message): void
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
        $user = $this->userRepository->findOneBy(['chat_id' => $chatId]);
        $user->setMode($userUpdate->getMode());

        $this->em->persist($user);
        $this->em->flush();
    }
}
