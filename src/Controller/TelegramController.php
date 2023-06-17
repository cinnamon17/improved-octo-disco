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

        if($update->getCallbackQuery()){

            $chat_id = $update->getCallbackQuery('from')['id'];
            $data = $update->getCallbackQuery();
            $user = $userRepository->findOneBy(['chat_id' => $chat_id]);
            $callbackQueryId = $update->getCallbackQuery('id');

            if($user){
                $user->setMode($data);
                $apiRequest->sendMessage(['chat_id' => $chat_id, 'text' => 'Modo configurado correctamente']);
                $entityManager->persist($user);
                $entityManager->flush();
            }

            $apiRequest->answerCallbackQuery($callbackQueryId);

            die();
        }

        if($update->getMessageText() == "/start") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $update->getWelcomeMessage()]);
            die();
        }

        if($update->getMessageText() == "/mode") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => '¿Que personaje te gustaria que interpretara? 🎭', 'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Super Mario🍄', 'callback_data' => 'Super Mario Bros'],
                        ['text' => 'Asistente 👨🏻‍🏫', 'callback_data' => 'asistente'],
                        ['text' => 'Traductor 👩‍🏫' , 'callback_data' => 'traductor']
                     ]]]])
            ;
            die();
        }

        $user = $userRepository->findOneBy(['chat_id' => $update->getChatId()]);
        $mode = $user->getMode() ?? 'asistente';
        $openaiResponse = $apiRequest->openApi($update->getMessageText(), $mode);
        $response = $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $openaiResponse]);


        if(!$user) {

            $user = new User();
            $user->setChatId($update->getChatId())
                 ->setIsBot($update->getIsBot())
                 ->setMode('asistente');
            $entityManager->persist($user);

        }

        $user->setFirstName($update->getFirstName())
             ->setLastName($update->getLastName())
             ->setUsername($update->getUsername());

        $message = new Message();
        $message->setText($update->getMessageText())
                ->setMessageId($update->getMessageId())
                ->setUser($user);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json($response);

    }
}
