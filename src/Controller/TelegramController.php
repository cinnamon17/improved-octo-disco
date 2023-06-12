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

        if($update->getMessageText() == "/mode") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => 'Selecciona un modo', 'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Psicologo', 'callback_data' => 'psicologo'],
                        ['text' => 'Asistente', 'callback_data' => 'asistente']
                     ]]]])
                    ;
            die();
        }

        $openaiResponse = $apiRequest->openApi($update->getMessageText());
        $response = $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $openaiResponse]);

        $user = $userRepository->findOneBy(['chat_id' => $update->getChatId()]);

        if(!$user) {

            $user = new User();
            $user->setChatId($update->getChatId());
            $user->setIsBot($update->getIsBot());
            $entityManager->persist($user);

        }

            $user->setFirstName($update->getFirstName());
            $user->setUsername($update->getUsername());
            $message = new Message();
            $message->setText($update->getMessageText());
            $message->setMessageId($update->getMessageId());
            $message->setUser($user);
            $entityManager->persist($message);
            $entityManager->flush();

        return $this->json($response);

    }
}
