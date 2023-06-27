<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\PromptRepository;
use App\Repository\UserRepository;
use App\Service\ApiRequest;
use App\Service\TelegramBotUpdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram', methods: 'post')]
    public function index(TranslatorInterface $translator, TelegramBotUpdate $update, ApiRequest $apiRequest, EntityManagerInterface $entityManager, UserRepository $userRepository, PromptRepository $promptRepository): JsonResponse
    {

        if($update->getCallbackQuery()){

            $chat_id = $update->getCallbackQuery('from')['id'];
            $data = $update->getCallbackQuery();
            $user = $userRepository->findOneBy(['chat_id' => $chat_id]);
            $callbackQueryId = $update->getCallbackQuery('id');

            if($user){
                $setModeMessage= $translator->trans('callbackQuery.message', locale: $update->getCallbackQuery('from')['language_code']);
                $user->setMode($data);
                $apiRequest->sendMessage(['chat_id' => $chat_id, 'text' => $setModeMessage]);
                $entityManager->persist($user);
                $entityManager->flush();
            }

            $apiRequest->answerCallbackQuery($callbackQueryId);

            die();
        }

        $welcomeMessage = $translator->trans('welcome.message', locale: $update->getLanguageCode());
        if($update->getMessageText() == "/start") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $welcomeMessage]);
            die();
        }

        $user = $userRepository->findOneBy(['chat_id' => $update->getChatId()]);
        $assistantMessage = $translator->trans('assistant.message', locale: $update->getLanguageCode());

        if(!$user) {

            $user = new User();
            $user->setChatId($update->getChatId())
                 ->setIsBot($update->getIsBot())
                 ->setMode($assistantMessage);
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

        $characterMessage = $translator->trans('character.message', locale: $update->getLanguageCode());
        $translatorMessage = $translator->trans('translator.message', locale: $update->getLanguageCode());
        $bussinessMessage= $translator->trans('business.message', locale: $update->getLanguageCode());
        if($update->getMessageText() == "/mode") {
            $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $characterMessage, 'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => $translatorMessage . " 🈯", 'callback_data' => $translatorMessage],
                        ['text' => $assistantMessage ." 👨🏻‍🏫",'callback_data' => $assistantMessage],
                     ],
                     [
                        ['text' => 'chef 🧑🏻‍🍳','callback_data' => 'chef'],
                        ['text' => 'doctor 👨🏻‍⚕️','callback_data' => 'doctor'],
                     ],
                     [
                        ['text' => $bussinessMessage . "💡",'callback_data' => 'startup'],
                     ]//,
                     //[
                      //  ['text' => 'video downloader' . "🎥",'callback_data' => 'downloader'],
                     //]

                    ]]])
            ;
            die();
        }

        if($user->getMode() == 'downloader'){
            $apiRequest->sendVideo($update->getMessageText());
            die();
        }

        $prompt = $promptRepository->findOneBy(['role' => $user->getMode(), 'language' => $update->getLanguageCode()]);

        if($prompt){
            $prompt = $prompt->getMessage();
        }else{
            $prompt = $assistantMessage;
        }

        $openaiResponse = $apiRequest->openApi($update->getMessageText(), $prompt);
        $response = $apiRequest->sendMessage(['chat_id' => $update->getChatId(), 'text' => $openaiResponse]);

        return $this->json($response);

    }
}
