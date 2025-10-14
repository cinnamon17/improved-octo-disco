<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\DBService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
class TelegramService implements LoggerAwareInterface
{
    private HttpService $http;
    private DBService $db;
    private LoggerInterface $logger;
    private TelegramDtoFactory $dtoFactory;
    private BotUpdateTranslator $bt;
    private MessageBusInterface $bus;

    public function __construct(
        HttpService $http,
        DBService $db,
        TelegramDtoFactory $telegramDtoFactory,
        BotUpdateTranslator $botUpdateTranslator,
	MessageBusInterface $bus
    ) {
        $this->http = $http;
        $this->db = $db;
        $this->dtoFactory = $telegramDtoFactory;
        $this->bt = $botUpdateTranslator;
	$this->bus = $bus;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function log(string $message, array $context = []): void
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
    }

    public function sendMessage(string $message): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message);
        $adminParams = $this->dtoFactory->createAdminSendMessageParams();
        $this->http->telegramRequest($adminParams);
        return $this->http->telegramRequest($params);
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->dtoFactory->createSendChatActionParams($action);
        return $this->http->telegramRequest($params);
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->dtoFactory->createAnswerCallbackQueryParams();
        return $this->http->telegramRequest($params);
    }

    public function sendInlineKeyboard(): JsonResponse
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams();
        return new JsonResponse($this->http->telegramRequest($params));
    }

    public function sendWelcomeMessage(): JsonResponse
    {
        $welcomeMessage = $this->bt->getWelcomeMessage();
        return new JsonResponse($this->sendMessage($welcomeMessage));
    }

    public function setBotMode(): void
    {
        $user = $this->dtoFactory->createUserBotMode();
        $this->db->updateUserMode($user);
    }

    public function handleCallbackQuery(): array
    {
	$params = $this->dtoFactory->createCallbackQueryParams();
	$this->setBotMode();
	$this->http->telegramRequest($params);

	$response =  $this->answerCallbackQuery();
	return $response;
    }

    public function handleIncomingMessage() : JsonResponse
    {
	$response = new JsonResponse(data: ["request" => "success"]);
	$response->send();
	$this->handleUserRegistration();
	$this->handleSpecialCommand();

	return new JsonResponse(['status' => 'ok']);

    }

    private function handleUserRegistration(): void
    {
	$chatId = $this->dtoFactory->createChatIdFromUpdate();

	if (!$this->db->isUserExists($chatId)) {
	    $user = $this->dtoFactory->createUser();
	    $this->db->insertUserInDb($user);
	} else {
	    $user = $this->dtoFactory->createUser();
	    $message = $this->dtoFactory->createMessage();
	    $this->db->updateUserInDb($user, $message);
	}
    }

    private function handleSpecialCommand(): JsonResponse
    {
	$text = $this->dtoFactory->createMessage();

	match($text->getText()) {
	    '/start' => $this->sendWelcomeMessage(),
	    '/mode' => $this->sendInlineKeyboard(),
	    default => $this->handleAIMessage()
	};
	return new JsonResponse(['status' => 'ok']);
    }

    private function handleAIMessage(): void
    {
	$this->sendChatAction('typing');
	$message = $this->dtoFactory->createChatPromptMessageDto($this->db);
	$this->bus->dispatch($message);

    }
}
