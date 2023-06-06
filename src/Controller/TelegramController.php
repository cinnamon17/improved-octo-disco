<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\ApiRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(Request $request, ApiRequest $apiRequest, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {

        $update = json_decode($request->getContent(), true);
        $chat_id = $update['message']['chat']['id'];
        $messageText = $update['message']['text'];
        $is_bot = $update['message']['from']['is_bot'];
        $first_name = $update['message']['from']['first_name'];
        $message_id = $update['message']['message_id'];

        $welcomeMessage = "¡Hola! Soy tu asistente de IA en Telegram. Estoy aquí para ayudarte en todo lo que necesites.\n
Si tienes preguntas, curiosidades o simplemente quieres charlar, ¡no dudes en escribirme! Estoy emocionado de comenzar esta aventura contigo.\n
¡Vamos a explorar el mundo de la inteligencia artificial juntos!";

        if(empty($messageText) || is_null($messageText)){
             $response = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => 'Por favor envia un texto valido']);
            die();
        }
        if($messageText == "/start"){

            $telegramResponse = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => $welcomeMessage]);
            die();
        }

        $response = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => '...']);
        $openaiResponse = $apiRequest->openApi($messageText);
        $message = $openaiResponse['choices'][0]['message']['content'];
        $response = $apiRequest->telegramApi('POST','sendMessage', ['chat_id' => $chat_id, 'text' => $message]);

        $user = $userRepository->findOneBy(['chat_id' => $chat_id]);

        if(!$user){

            $user = new User();
            $user->setChatId($chat_id);
            $user->setIsBot($is_bot);
            $user->setFirstName($first_name);
            $entityManager->persist($user);
            $entityManager->flush();

        }else{

            $message = new Message();
            $message->setText($messageText);
            $message->setMessageId($message_id);
            $user->addMessage($message);
            $entityManager->persist($message);
            $entityManager->flush();

        }

        return $this->json($response);

    }
}
