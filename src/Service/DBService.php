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

    public function __construct(EntityManagerInterface $entityManagerInterface, UserRepository $userRepository, PromptRepository $promptRepository)
    {
        $this->em = $entityManagerInterface;
        $this->userRepository = $userRepository;
        $this->promptRepository = $promptRepository;
    }

    public function userFindOneBy(string $chatId): ?User
    {
        $user = $this->userRepository->findOneBy(['chat_id' => $chatId]);
        return $user;
    }

    public function promptFindOneBy(string $role, string $lang): Prompt
    {
        $prompt = $this->promptRepository->findOneBy(['role' => $role, 'language' => $lang]);
        return $prompt;
    }

    public function getPrompt(BotUpdateTranslator $but): Prompt
    {
        $chatId = $but->update()->getChatId();
        $user = $this->userFindOneBy($chatId);
        $prompt = $this->promptFindOneBy($user->getMode(), $but->update()->getLanguageCode());

        return $prompt;
    }

    public function isUserExists(BotUpdateTranslator $but): bool
    {
        $chatId = $but->update()->getChatId() ?? $but->update()->getCallbackQuery()->getFrom()->getId();
        $user = $this->userFindOneBy($chatId);
        $existsUser = $user ? true : false;

        return $existsUser;
    }

    public function insertUserInDb(BotUpdateTranslator $but): void
    {
        $user = new User();
        $user->setChatId($but->update()->getChatId())
            ->setIsBot($but->update()->getIsBot())
            ->setMode($but->getAssistantMessage())
            ->setFirstName($but->update()->getFirstName());

        $this->save($user);
    }

    public function updateUserInDb(BotUpdateTranslator $but): void
    {
        $user = $this->userFindOneBy($but->update()->getChatId());
        $user->setFirstName($but->update()->getFirstName())
            ->setLastName($but->update()->getLastName())
            ->setUsername($but->update()->getUsername());

        $message = new Message();
        $message->setText($but->update()->getMessageText())
            ->setMessageId($but->update()->getMessageId())
            ->setUser($user);

        $this->save($message);
    }

    public function setBotMode(BotUpdateTranslator $but)
    {
        $chatId = $but->update()->getChatId() ?? $but->update()->getCallbackQuery()->getFrom()->getId();
        $user = $this->userFindOneBy($chatId);
        $user->setMode($but->update()->getCallbackQueryData());
        $this->save($user);
    }

    public function persist(Object $object): void
    {
        $this->em->persist($object);
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function save(Object $object): void
    {
        $this->em->persist($object);
        $this->em->flush();
    }
}
