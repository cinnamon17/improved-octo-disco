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
    private TelegramBotUpdate $update;
    private TelegramClient $client;

    public function __construct(
	TelegramClient $client,
        DBService $db,
        TelegramDtoFactory $telegramDtoFactory,
        BotUpdateTranslator $botUpdateTranslator,
	MessageBusInterface $bus,
	TelegramBotUpdate $update,

    ) {
	$this->client = $client;
        $this->db = $db;
        $this->dtoFactory = $telegramDtoFactory;
        $this->bt = $botUpdateTranslator;
	$this->bus = $bus;
	$this->update = $update;
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
	$this->client->sendAdminMessageFromUpdate($this->update);
        return $this->client->sendMessage($message, $this->update);
    }

    public function sendChatAction(string $action): array
    {
	return $this->client->sendChatAction($action, $this->update);
    }

    public function answerCallbackQuery(): array
    {
        return $this->client->answerCallbackQuery($this->update);
    }

    public function sendInlineKeyboard(): JsonResponse
    {
        return new JsonResponse($this->client->sendInlineKeyboard($this->update));
    }

    public function sendWelcomeMessage(): JsonResponse
    {
        $welcomeMessage = $this->bt->getWelcomeMessage();
        return new JsonResponse($this->sendMessage($welcomeMessage));
    }

    public function setBotMode(): void
    {
        $user = $this->dtoFactory->createUserBotMode($this->update);
        $this->db->updateUserMode($user);
    }

    public function handleCallbackQuery(): JsonResponse
    {
	$params = $this->dtoFactory->createCallbackQueryParams($this->update);

	$this->setBotMode();
	$this->http->telegramRequest($params);

	$this->answerCallbackQuery();
	return new JsonResponse( ['status' => 'callback handled']);
    }

    public function handleIncomingMessage() : JsonResponse
    {
	$this->handleUserRegistration();
	$this->handleSpecialCommand();

	return new JsonResponse(['status' => 'ok']);

    }

    private function handleUserRegistration(): void
    {
	$chatId = $this->dtoFactory->createChatIdFromUpdate($this->update);

	if (!$this->db->isUserExists($chatId)) {
	    $user = $this->dtoFactory->createUser($this->update);
	    $this->db->insertUserInDb($user);
	} else {
	    $user = $this->dtoFactory->createUser($this->update);
	    $message = $this->dtoFactory->createMessage($this->update);
	    $this->db->updateUserInDb($user, $message);
	}
    }

    private function handleSpecialCommand(): JsonResponse
    {
	$text = $this->dtoFactory->createMessage($this->update);

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
	$message = $this->dtoFactory->createChatPromptMessageDto($this->db, $this->update);
	$this->bus->dispatch($message);

    }
}
