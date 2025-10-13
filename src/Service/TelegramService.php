<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Dto\TelegramDtoInterface;
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

    public function telegramRequest(TelegramDtoInterface $dto): array
    {
        return $this->http->request($dto);
    }

    public function sendMessage(string $message): array
    {
        $params = $this->dtoFactory->createSendMessageParams($message);
        $adminParams = $this->dtoFactory->createAdminSendMessageParams();
        $this->telegramRequest($adminParams);
        return $this->telegramRequest($params);
    }

    public function sendChatAction(string $action): array
    {
        $params = $this->dtoFactory->createSendChatActionParams($action);
        return $this->telegramRequest($params);
    }

    public function answerCallbackQuery(): array
    {
        $params = $this->dtoFactory->createAnswerCallbackQueryParams();
        return $this->telegramRequest($params);
    }

    public function sendInlineKeyboard(): JsonResponse
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams();
        return new JsonResponse($this->telegramRequest($params));
    }

    public function sendWelcomeMessage(): JsonResponse
    {
        $welcomeMessage = $this->bt->getWelcomeMessage();
        return new JsonResponse($this->sendMessage($welcomeMessage));
    }

    public function chatCompletion(): array
    {
        $this->sendChatAction('typing');
        $chatPromptMessageDto = $this->dtoFactory->createChatPromptMessageDto($this->db);
        return $this->http->chatCompletion($chatPromptMessageDto);
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
	$this->telegramRequest($params);

	$response =  $this->answerCallbackQuery();
	return $response;
    }

    public function handleIncomingMessage() : JsonResponse
    {
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
