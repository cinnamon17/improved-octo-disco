<?php

namespace App\Service;

use App\Dto\SendAIMessageCommandDto;
use App\Dto\TelegramBotUpdate;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
class TelegramService implements LoggerAwareInterface
{
    private UserManagementService $userManagementService;
    private DomainDtoFactory $domainDtoFactory;         
    private LoggerInterface $logger;
    private BotUpdateTranslator $bt;
    private MessageBusInterface $bus;
    private TelegramClient $client;

    public function __construct(
	TelegramClient $client,
	UserManagementService $userManagementService,
	DomainDtoFactory $domainDtoFactory,
	BotUpdateTranslator $botUpdateTranslator,
	MessageBusInterface $bus,
    ) {
	$this->client = $client;
	$this->userManagementService = $userManagementService;
	$this->domainDtoFactory = $domainDtoFactory;
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

    public function sendMessage(TelegramBotUpdate $update, string $message): array
    {
	$this->client->sendAdminMessageFromUpdate($update);
	return $this->client->sendMessage($message, $update);
    }

    public function sendWelcomeMessage(TelegramBotUpdate $update): array
    {
	$welcomeMessage = $this->bt->getWelcomeMessage($update->getLocale());
	return $this->sendMessage($update, $welcomeMessage);
    }

    public function setBotMode(TelegramBotUpdate $update): void
    {
	$user = $this->domainDtoFactory->createUserBotMode($update);
	$this->userManagementService->updateUserMode($user);
    }

    public function handleCallbackQuery(TelegramBotUpdate $update): array
    {
	$this->setBotMode($update);

	$response = $this->client->sendCallbackQueryResponse($update); 

	$this->client->answerCallbackQuery($update);
	return $response; 
    }

    public function handleIncomingMessage(TelegramBotUpdate $update): array
    {
	$this->handleUserRegistration($update);
	return $this->handleSpecialCommand($update);

    }

    private function handleUserRegistration(TelegramBotUpdate $update): void
    {
	$user = $this->domainDtoFactory->createUser($update);
	$message = $this->domainDtoFactory->createMessage($update);

	$this->userManagementService->handleIncomingUser($user, $message);
    }

    private function handleSpecialCommand(TelegramBotUpdate $update): array
    {
	$text = $this->domainDtoFactory->createMessage($update);

	return match($text->getText()) {
	    '/start' => $this->sendWelcomeMessage($update),
	    '/mode' => $this->client->sendInlineKeyboard($update),
	    default => $this->handleAIMessage($update)
	};
    }

    private function handleAIMessage(TelegramBotUpdate $update): array
    {
	$this->client->sendChatAction('typing', $update);
	$user = $this->userManagementService->findUserByChatId($update->getChatId());
	$userMode = $user ? $user->getMode() : $this->bt->getAssistantMessage($update->getLocale());
	$chatDto = $this->domainDtoFactory->createChatPromptMessageDto($update, $userMode);
	$command = new SendAIMessageCommandDto($chatDto, $update->getChatId()); 

	$this->bus->dispatch($command);

	return ['status' => 'AI message dispatched'];
    }
}
