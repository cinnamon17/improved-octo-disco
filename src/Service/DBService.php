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
    private TelegramBotUpdate $update;
    private BotUpdateTranslator $bt;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        PromptRepository $promptRepository,
        TelegramBotUpdate $update,
        BotUpdateTranslator $bt
    ) {
        $this->em = $entityManagerInterface;
        $this->userRepository = $userRepository;
        $this->promptRepository = $promptRepository;
        $this->update = $update;
        $this->bt = $bt;
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

    public function updateUserInDb(): void
    {
        $user = $this->userRepository->findOneBy(['chat_id' => $this->update->getChatId()]);
        $user->setFirstName($this->update->getFirstName())
            ->setLastName($this->update->getLastName())
            ->setUsername($this->update->getUsername());

        $message = (new Message())
            ->setText($this->update->getMessageText())
            ->setMessageId($this->update->getMessageId())
            ->setUser($user);

        $this->em->persist($message);
        $this->em->flush();
    }

    public function updateUserMode(User $user): void
    {
        $chatId = $user->getChatId();
        $user = $this->userRepository->findOneBy(['chat_id' => $chatId]);
        $user->setMode($user->getMode());

        $this->em->persist($user);
        $this->em->flush();
    }
}
