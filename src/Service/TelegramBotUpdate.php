<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class TelegramBotUpdate
{
    private array $request;
    private String $welcomeMessage;


    public function __construct(RequestStack $request, String $welcomeMessage )
    {
        $this->request = $request->getCurrentRequest()->toArray();
        $this->welcomeMessage = $welcomeMessage;
    }

    public function getUpdateId(): float {

        $update_id = $this->request['update_id'];
        return $update_id;

    }

    public function getMessageText(): ?string {

        $message = $this->request['message']['text'] ??  null;
        return $message;

    }

    public function getMessageId(): ?float {

        $message_id = $this->request['message']['message_id'] ?? null;
        return $message_id;

    }

    public function getChatId(): ?float {

        $chat_id = $this->request['message']['chat']['id'] ?? null;
        return $chat_id;

    }

    public function getIsBot(): ?bool {

        $is_bot = $this->request['message']['from']['is_bot'] ?? null;
        return $is_bot;

    }

    public function getFirstName(): ?string{

        $first_name= $this->request['message']['from']['first_name'] ?? null;
        return $first_name;

    }

    public function getLastName(): ?string{

        $first_name= $this->request['message']['from']['last_name'] ?? null;
        return $first_name;

    }

    public function getUsername(): ?string{

        $first_name= $this->request['message']['from']['username'] ?? null;
        return $first_name;

    }

    public function getWelcomeMessage(): String{

        return $this->welcomeMessage;

    }

    public function getCallbackQuery(string $id = 'data'): mixed{

        $callbackQueryData = $this->request['callback_query']["$id"] ?? null;
        return $callbackQueryData;

    }
}
