<?php

namespace App\Service;

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

    public function __construct(
        HttpService $http,
        DBService $db,
        TelegramDtoFactory $telegramDtoFactory,
        BotUpdateTranslator $botUpdateTranslator
    ) {
        $this->http = $http;
        $this->db = $db;
        $this->dtoFactory = $telegramDtoFactory;
        $this->bt = $botUpdateTranslator;
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

    public function sendInlineKeyboard(): array
    {
        $params = $this->dtoFactory->createSendInlineKeyboardParams();
        return $this->telegramRequest($params);
    }

    public function sendWelcomeMessage(): array
    {
        $welcomeMessage = $this->bt->getWelcomeMessage();
        return $this->sendMessage($welcomeMessage);
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

    public function handleIncomingMessage(): array
    {
	$this->handleUserRegistration();

	if ($this->isSpecialCommand()) {
	    return $this->handleSpecialCommand();
	}

	return $this->handleAIMessage();
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

    private function isSpecialCommand(): bool
    {
	$text = $this->dtoFactory->createMessage();
	return in_array($text, ['/start', '/mode']);
    }

    private function handleSpecialCommand(): array
    {
	$text = $this->dtoFactory->createMessage();

	return match($text) {
	    '/start' => $this->sendWelcomeMessage(),
	    '/mode' => $this->sendInlineKeyboard(),
	    default => throw new \InvalidArgumentException('Unknown command')
	};
    }

    private function handleAIMessage(): array
    {
	$this->sendChatAction('typing');
	$openaiResponse = $this->chatCompletion();

	if (strlen($openaiResponse["choices"][0]["message"]["content"]) < 4096) {
	    return $this->sendMessage($openaiResponse["choices"][0]["message"]["content"]);
	} else {
	    $chunks = str_split($openaiResponse["choices"][0]["message"]["content"], 4096);
	    foreach ($chunks as $text) {
		$lastResponse = $this->sendMessage($text);
	    }
	    return $lastResponse;
	}
    }
}
