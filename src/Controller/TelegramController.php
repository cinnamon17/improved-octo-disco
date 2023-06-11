<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ApiRequest;
use App\Service\TelegramBotUpdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(TelegramBotUpdate $update, ApiRequest $apiRequest, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {


        if($update->getMessageText() == "/start") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $update->getWelcomeMessage()]);
            die();
        }

        $openaiResponse = $apiRequest->openApi($update->getMessageText());
        $response = $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $openaiResponse]);

        $user = $userRepository->findOneBy(['chat_id' => $update->getChatId()]);

        if(!$user) {

            $message = new Message();
            $message->setText($update->getMessageText());
            $message->setMessageId($update->getMessageId());

            $user = new User();
            $user->setChatId($update->getChatId());
            $user->setIsBot($update->getIsBot());
            $user->setFirstName($update->getFirstName());
            $user->setUsername($update->getUsername());
            $user->addMessage($message);

            $entityManager->persist($message);
            $entityManager->persist($user);
            $entityManager->flush();

        } else {

            $message = new Message();
            $message = new Message();
            $message->setText($update->getMessageText());
            $message->setMessageId($update->getMessageId());
            $message->setUser($user);
            $entityManager->persist($message);
            $entityManager->flush();

        }

        return $this->json($response);

    }
}
