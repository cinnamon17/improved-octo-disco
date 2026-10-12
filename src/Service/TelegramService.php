<?php

namespace App\Service;

use App\Dto\SendAIMessageCommandDto;
use App\Dto\TelegramBotUpdate;
use App\Exception\InsufficientCreditsException;
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
    private AiConsumptionService $consumptionService;
    private TelegramPaymentService $paymentService;
    private CreditPriceCalculator $calculator;
    private string $paySupportContact;

    public function __construct(
        TelegramClient $client,
        UserManagementService $userManagementService,
        DomainDtoFactory $domainDtoFactory,
        BotUpdateTranslator $botUpdateTranslator,
        MessageBusInterface $bus,
        AiConsumptionService $consumptionService,
        TelegramPaymentService $paymentService,
        CreditPriceCalculator $calculator,
        string $paySupportContact = '',
    ) {
        $this->client = $client;
        $this->userManagementService = $userManagementService;
        $this->domainDtoFactory = $domainDtoFactory;
        $this->bt = $botUpdateTranslator;
        $this->bus = $bus;
        $this->consumptionService = $consumptionService;
        $this->paymentService = $paymentService;
        $this->calculator = $calculator;
        $this->paySupportContact = $paySupportContact;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function log(string $message, array $context = []): void
    {
        $this->logger->info('File: TelegramService.php ' . $message, $context);
    }

    public function sendWelcomeMessage(TelegramBotUpdate $update): array
    {
        $welcomeMessage = $this->bt->getWelcomeMessage($update->getLocale());
        return $this->client->sendMessage($welcomeMessage, $update);
    }

    public function setBotMode(TelegramBotUpdate $update): void
    {
        $user = $this->domainDtoFactory->createUserBotMode($update);
        $this->userManagementService->updateUserMode($user);
    }

    public function handleCallbackQuery(TelegramBotUpdate $update): array
    {
        // Los botones de recarga lanzan el flujo de pago, no cambian el modo.
        if ($this->isTopUpCallback($update)) {
            return $this->handleTopUpCallback($update);
        }

        $this->setBotMode($update);

        $response = $this->client->sendCallbackQueryResponse($update);

        $this->client->answerCallbackQuery($update);
        return $response;
    }

    private function isTopUpCallback(TelegramBotUpdate $update): bool
    {
        return str_starts_with((string) $update->getCallbackQueryData(), 'topup:');
    }

    private function handleTopUpCallback(TelegramBotUpdate $update): array
    {
        $chatId = $update->getCallbackQueryChatId();
        $stars = (int) substr((string) $update->getCallbackQueryData(), strlen('topup:'));
        $locale = $update->getLocale() ?? 'en';

        $this->client->answerCallbackQuery($update);

        $user = $this->userManagementService->findUserByChatId($chatId);

        if ($user === null || $stars <= 0) {
            return ['status' => 'unknown_topup_target'];
        }

        // El precio sale del boton, nunca del texto del usuario.
        return $this->paymentService->createInvoice(
            $user,
            $stars,
            $this->bt->getInvoiceTitle($locale),
            $this->bt->getTopUpMessage($locale),
            $locale
        );
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
        $this->client->sendAdminMessageFromUpdate($update);

        return match($text->getText()) {
        '/start' => $this->sendWelcomeMessage($update),
            '/mode' => $this->client->sendInlineKeyboard($update),
            '/topup' => $this->client->sendTopUpKeyboard($update),
            '/balance' => $this->sendBalance($update),
            // Antes que handleAIMessage y por el mismo camino que /balance:
            // un usuario sin saldo no puede permitirse que le cobren por
            // preguntar donde reclamar. Telegram exige /paysupport en todo
            // bot que acepte pagos.
            '/paysupport' => $this->sendPaySupport($update),
            default => $this->handleAIMessage($update)
        };
    }

    private function sendPaySupport(TelegramBotUpdate $update): array
    {
        $user = $this->userManagementService->findUserByChatId($update->getChatId());

        $this->client->sendGenericMessage(
            $this->bt->getPaySupportMessage(
                $update->getLocale() ?? 'en',
                $this->paySupportContact,
                $user?->getCredits() ?? 0,
                $this->consumptionService->getRequestPrice()
            ),
            $update->getChatId()
        );

        return ['status' => 'paysupport_sent'];
    }

    private function handleAIMessage(TelegramBotUpdate $update): array
    {
        $this->client->sendChatAction('typing', $update);
        $user = $this->userManagementService->findUserByChatId($update->getChatId());

        if ($user === null) {
            return ['status' => 'unknown_user'];
        }

        $userMode = $user->getMode() ?? $this->bt->getAssistantMessage($update->getLocale());

        try {
            // La reserva va antes de la llamada a la IA. Es lo que hace que N
            // peticiones simultaneas no puedan pasar todas el check con el
            // mismo saldo.
            $log = $this->consumptionService->reserve(
                $user,
                $userMode,
                (string) $update->getMessageId()
            );
        } catch (InsufficientCreditsException $e) {
            $this->client->sendInsufficientCreditsMessage(
                $update,
                $this->bt->getInsufficientCreditsMessage(
                    $update->getLocale() ?? 'en',
                    $user->getCredits(),
                    $this->consumptionService->getRequestPrice()
                )
            );

            return ['status' => 'insufficient_credits', 'credits' => $user->getCredits()];
        }

        $chatDto = $this->domainDtoFactory->createChatPromptMessageDto($update, $userMode);
        $command = new SendAIMessageCommandDto($chatDto, $update->getChatId(), $log->getId());

        $this->bus->dispatch($command);

        return ['status' => 'AI message dispatched'];
    }

    private function sendBalance(TelegramBotUpdate $update): array
    {
        $user = $this->userManagementService->findUserByChatId($update->getChatId());

        if ($user === null) {
            return ['status' => 'unknown_user'];
        }

        $this->client->sendGenericMessage(
            $this->bt->getBalanceMessage($update->getLocale(), $user->getCredits()),
            $update->getChatId()
        );

        return ['status' => 'balance_sent', 'credits' => $user->getCredits()];
    }
}
