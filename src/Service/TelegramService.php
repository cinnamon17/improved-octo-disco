<?php

namespace App\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
use App\Service\DBService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class TelegramService implements LoggerAwareInterface
{
    private HttpService $http;
    private BotUpdateTranslator $bt;
    private DBService $db;
    private LoggerInterface $logger;

    public function __construct(HttpService $http, BotUpdateTranslator $bt, DBService $db)
    {
        $this->http = $http;
        $this->bt = $bt;
        $this->db = $db;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function telegramRequest(array $params): array
    {
        $response = $this->http->request($params);
        return $response;
    }

    public function handleCallbackQuery(): array
    {
        $params = $this->callbackQueryParams();
        $this->setBotMode();
        $this->telegramRequest($params);

        $response =  $this->answerCallbackQuery();
        return $response;
    }

    public function sendMessage(string $message): array
    {
        $params = $this->sendMessageParams($message);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->SendChatActionParams($action);
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->answerCallbackQueryParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendInlineKeyboard(): array
    {
        $params = $this->sendInlineKeyboardParams();
        $response = $this->telegramRequest($params);
        return $response;
    }

    public function sendWelcomeMessage(): array
    {
        $welcomeMessage = $this->bt->translate('welcome.message');
        $response = $this->sendMessage($welcomeMessage);
        return $response;
    }

    public function isUserExists(): bool
    {
        $bool = $this->db->isUserExists($this->bt);
        return $bool;
    }

    public function isCallbackQuery(): bool
    {
        $data = $this->getCallbackQuery();
        return $data ? true : false;
    }

    public function chatCompletion($message): array
    {
        $this->sendChatAction('typing');
        $prompt = $this->db->getPrompt($this->bt);
        $chatPromptMessageDto = new ChatPromptMessageDto();
        $chatPromptMessageDto->setMessage($message)
            ->setPrompt($prompt->getRole());
        return $this->http->chatCompletion($chatPromptMessageDto);
    }

    public function setBotMode(): void
    {
        $this->db->setBotMode($this->bt);
    }

    public function translate(string $message): string
    {
        return $this->bt->translate($message);
    }

    public function log($message, $context = [])
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
    }

    public function getChatId(): ?float
    {
        return $this->bt->update()->getChatId();
    }

    public function getFirstName(): ?string
    {
        return $this->bt->update()->getFirstName();
    }

    public function getLastname(): ?string
    {
        return $this->bt->update()->getLastName();
    }

    public function getUsername(): ?string
    {
        return $this->bt->update()->getUsername();
    }

    public function getMessageId()
    {
        return $this->bt->update()->getMessageId();
    }

    public function getMessageText(): ?string
    {
        return $this->bt->update()->getMessageText();
    }

    public function getCallbackQuery()
    {
        return $this->bt->update()->getCallbackQuery();
    }

    public function getCallbackQueryId()
    {
        return $this->bt->update()->getCallbackQueryId();
    }

    public function getCallbackQueryChatId()
    {
        return $this->bt->update()->getCallbackQueryChatId();
    }

    public function getCallbackQueryData()
    {
        return $this->bt->update()->getCallbackQueryData();
    }

    public function getLanguageCode()
    {
        return $this->bt->update()->getLanguageCode();
    }

    public function getIsBot(): bool
    {
        return $this->bt->update()->getIsBot();
    }

    public function insertUserInDb(): void
    {
        $this->db->insertUserInDb($this->bt);
    }

    public function updateUserInDb(): void
    {
        $this->db->updateUserInDb($this->bt);
    }

    private function callbackQueryParams(): array
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message');
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto->setMethod('sendMessage')
            ->setChatId($this->getCallbackQueryChatId())
            ->setText($setModeMessage);

        return $telegramMessageDto->toArray();
    }

    private function sendMessageParams(string $message): array
    {
        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto
            ->setChatId($this->getChatId())
            ->setMethod('sendMessage')
            ->setText($message);

        return $telegramMessageDto->toArray();
    }

    private function sendChatActionParams(string $action)
    {
        $telegramActionDto = new TelegramActionDto();
        $telegramActionDto
            ->setChatId($this->getChatId())
            ->setMethod('sendChatAction')
            ->setAction($action);

        return $telegramActionDto->toArray();
    }
    private function answerCallbackQueryParams(): array
    {
        $answerCallbackQueryDto = new AnswerCallbackQueryDto();
        $answerCallbackQueryDto
            ->setId($this->getCallbackQueryId())
            ->setMethod('answerCallbackQuery');

        return $answerCallbackQueryDto->toArray();
    }

    private function sendInlineKeyboardParams(): array
    {
        $translatorButton = new InlineKeyboardButtonDto();
        $translatorButton
            ->setText($this->bt->getTranslatorMessage() . " 🈯")
            ->setData($this->bt->getTranslatorMessage());

        $assistantButton = new InlineKeyboardButtonDto();
        $assistantButton
            ->setText($this->bt->getAssistantMessage() .  " 👨🏻‍🏫")
            ->setData($this->bt->getAssistantMessage());

        $assistantButton = new InlineKeyboardButtonDto();
        $assistantButton
            ->setText($this->bt->getAssistantMessage() .  " 👨🏻‍🏫")
            ->setData($this->bt->getAssistantMessage());

        $cheffButton = new InlineKeyboardButtonDto();
        $cheffButton
            ->setText('chef 🧑🏻‍🍳')
            ->setData('chef');

        $doctorButton = new InlineKeyboardButtonDto();
        $doctorButton
            ->setText('doctor 👨🏻‍⚕️')
            ->setData('doctor');

        $bussinessButton = new InlineKeyboardButtonDto();
        $bussinessButton
            ->setText($this->bt->getbussinessMessage() . '💡')
            ->setData('startup');

        $buttonRow1 = new InlineKeyboardButtonRowDto();
        $buttonRow1
            ->add($translatorButton)
            ->add($assistantButton);

        $buttonRow2 = new InlineKeyboardButtonRowDto();
        $buttonRow2
            ->add($cheffButton)
            ->add($doctorButton);

        $buttonRow3 = new InlineKeyboardButtonRowDto();
        $buttonRow3
            ->add($bussinessButton);

        $inlineKeyboardDto = new InlineKeyboardDto();
        $inlineKeyboardDto
            ->add($buttonRow1)
            ->add($buttonRow2)
            ->add($buttonRow3);

        $telegramMessageDto = new TelegramMessageDto();
        $telegramMessageDto
            ->setMethod('sendMessage')
            ->setChatId($this->getChatId())
            ->setText($this->bt->getCharacterMessage())
            ->setReplyMarkup($inlineKeyboardDto);

        return $telegramMessageDto->toArray();
    }
}
