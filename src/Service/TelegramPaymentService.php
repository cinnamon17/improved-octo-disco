<?php

namespace App\Service;

use App\Dto\AnswerPreCheckoutQueryDto;
use App\Dto\SendInvoiceDto;
use App\Dto\TelegramBotUpdate;
use App\Entity\StarTransaction;
use App\Entity\User;
use App\Repository\StarTransactionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Emision y liquidacion de facturas de Telegram Stars.
 */
class TelegramPaymentService implements LoggerAwareInterface
{
    private EntityManagerInterface $em;
    private Connection $conn;
    private StarTransactionRepository $repository;
    private TelegramClient $telegramClient;
    private TelegramApiDtoFactory $dtoFactory;
    private CreditPriceCalculator $calculator;
    private CreditManager $creditManager;
    private BotUpdateTranslator $bt;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        Connection $conn,
        StarTransactionRepository $repository,
        TelegramClient $telegramClient,
        TelegramApiDtoFactory $dtoFactory,
        CreditPriceCalculator $calculator,
        CreditManager $creditManager,
        BotUpdateTranslator $bt,
    ) {
        $this->em = $em;
        $this->conn = $conn;
        $this->repository = $repository;
        $this->telegramClient = $telegramClient;
        $this->dtoFactory = $dtoFactory;
        $this->calculator = $calculator;
        $this->creditManager = $creditManager;
        $this->bt = $bt;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Registra la factura en PENDING y la envia. La fila existe antes de que
     * Telegram sepa nada, para que el pre_checkout_query se valide contra
     * nuestra propia DB y no contra lo que el cliente diga.
     */
    public function createInvoice(User $user, int $stars, string $title, string $description, ?string $locale = null): array
    {
        $payload = strtoupper(bin2hex(random_bytes(8)));
        $locale = $locale ?? 'en';

        $transaction = new StarTransaction();
        $transaction->setUser($user)
            ->setInvoicePayload($payload)
            // Placeholder unico derivado del payload: la columna es UNIQUE y
            // se reemplaza por el charge id real en el successful_payment.
            ->setTelegramChargeId('pending-' . $payload)
            ->setCurrency('XTR')
            ->setTotalAmount($stars)
            ->setCredits($this->calculator->starsToCredits($stars))
            ->setStatus(StarTransaction::STATUS_PENDING);

        $sent = $this->conn->transactional(function () use ($transaction, $user, $stars, $title, $description, $payload, $locale) {
            $this->em->persist($transaction);
            $this->em->flush();

            $response = $this->telegramClient->sendInvoice(
                (int) $user->getChatId(),
                $payload,
                $title,
                $description,
                $stars,
                $this->bt->getInvoiceLabel($locale, $stars)
            );

            // Una factura que Telegram rechazo no puede quedarse en PENDING:
            // el pre_checkout_query valida contra PENDING, asi que ahi la
            // dariamos por buena un invoice que nunca existio.
            if (($response['ok'] ?? true) === false) {
                $transaction->setStatus(StarTransaction::STATUS_FAILED)
                    ->setRawPayload([
                        'send_invoice_response' => $response,
                    ]);
                $this->em->flush();

                return false;
            }

            return true;
        });

        if (!$sent) {
            $this->logger->error('sendInvoice rechazado por Telegram, factura marcada como FAILED', [
                'invoice_payload' => $payload,
                'chat_id' => $user->getChatId(),
                'stars' => $stars,
            ]);

            return [
                'status' => 'invoice_failed',
                'invoice_payload' => $payload,
                'stars' => $stars,
            ];
        }

        return ['status' => 'invoice_sent', 'invoice_payload' => $payload, 'stars' => $stars];
    }

    /**
     * Resolucion de pre_checkout_query. Telegram da 10 segundos y reintenta si
     * no respondemos, asi que aqui solo hay validacion local: nada de IA,
     * nada de mensajes al usuario, nada de HTTP saliente salvo la respuesta.
     *
     * No comprueba saldo: el pago de una recarga no depende de tener creditos.
     * Comprueba integridad de la factura.
     */
    public function handlePreCheckoutQuery(TelegramBotUpdate $update): array
    {
        $preCheckout = $update->getPreCheckoutQuery();
        $transaction = $this->repository->findByInvoicePayload($preCheckout->getInvoicePayload());

        $ok = $transaction !== null
            && $transaction->isPending()
            && $preCheckout->getCurrency() === $transaction->getCurrency()
            && $preCheckout->getTotalAmount() === $transaction->getTotalAmount()
            && $preCheckout->getFrom()->getId() === $transaction->getUser()->getChatId();

        if (!$ok && $transaction === null) {
            $this->logger->warning('pre_checkout_query con payload desconocido', [
                'invoice_payload' => $preCheckout->getInvoicePayload(),
                'update_id' => $update->getUpdateId(),
            ]);
        }

        $errorMessage = $ok
            ? null
            : $this->bt->getPaymentInvalidMessage($update->getLocale() ?? 'en');

        $this->answerPreCheckoutQuery($preCheckout->getId(), $ok, $errorMessage);

        return ['status' => $ok ? 'pre_checkout_accepted' : 'pre_checkout_rejected'];
    }

    public function answerPreCheckoutQuery(string $queryId, bool $ok, ?string $errorMessage = null): void
    {
        $dto = (new AnswerPreCheckoutQueryDto())
            ->setPreCheckoutQueryId($queryId)
            ->setOk($ok)
            ->setErrorMessage($errorMessage);

        $this->telegramClient->answerPreCheckoutQuery($dto);
    }

    /**
     * Acredita la recarga. Idempotente por estado mas el UNIQUE de
     * telegram_charge_id, que es lo que corta los webhooks reintentados y los
     * dobles click en el boton.
     */
    public function handleSuccessfulPayment(TelegramBotUpdate $update): array
    {
        $payment = $update->getSuccessfulPayment();

        $alreadyCredited = false;

        $this->conn->transactional(function () use ($payment, $update, &$alreadyCredited) {
            $transaction = $this->repository->findByInvoicePayload($payment->getInvoicePayload());

            if ($transaction === null) {
                $this->logger->error('successful_payment con payload desconocido', [
                    'invoice_payload' => $payment->getInvoicePayload(),
                    'update_id' => $update->getUpdateId(),
                ]);

                return;
            }

            if ($transaction->isCompleted()) {
                $alreadyCredited = true;
                return;
            }

            $transaction->setTelegramChargeId($payment->getTelegramPaymentChargeId())
                ->setStatus(StarTransaction::STATUS_COMPLETED)
                ->setProviderChargeId($payment->getProviderPaymentChargeId())
                ->setRawPayload($update->toRawArray());

            try {
                $this->em->flush();
            } catch (UniqueConstraintViolationException $e) {
                // Carrera real: dos webhooks del mismo charge id a la vez.
                // El UNIQUE de telegram_charge_id es la garantia final; el
                // estado ya estaba COMPLETED en la otra transaccion.
                $alreadyCredited = true;
                $this->em->clear();

                return;
            }

            $this->creditManager->credit($transaction->getUser(), (int) $transaction->getCredits());
        });

        if ($alreadyCredited) {
            $this->logger->info('Pago duplicado ignorado, ya acreditado', [
                'telegram_charge_id' => $payment->getTelegramPaymentChargeId(),
            ]);

            return ['status' => 'payment_already_credited'];
        }

        $this->logger->info('Recarga acreditada', [
            'telegram_charge_id' => $payment->getTelegramPaymentChargeId(),
            'total_amount' => $payment->getTotalAmount(),
        ]);

        return ['status' => 'payment_credited', 'stars' => $payment->getTotalAmount()];
    }

    /**
     * refunded_payment: Stars devueltas implican creditos devueltos.
     */
    public function handleRefundedPayment(TelegramBotUpdate $update): array
    {
        $refund = $update->getRefundedPayment();
        $transaction = $this->repository->findByChargeId($refund->getTelegramPaymentChargeId());

        if ($transaction === null || $transaction->getStatus() === StarTransaction::STATUS_REFUNDED) {
            return ['status' => 'refund_ignored'];
        }

        $user = $transaction->getUser();

        try {
            $this->creditManager->debit($user, (int) $transaction->getCredits());
            $this->telegramClient->refundStarPayment((int) $user->getChatId(), $refund->getTelegramPaymentChargeId());
        } catch (\Throwable $e) {
            // El usuario ya consumio lo que le quedaba. Queda en deuda y la
            // guarda de reserve() lo bloquea hasta que reponga.
            $this->logger->warning('Reembolso sin saldo para compensar', [
                'user_id' => $user->getId(),
                'credits' => $transaction->getCredits(),
            ]);
        }

        $transaction->setStatus(StarTransaction::STATUS_REFUNDED)
            ->setRawPayload($update->toRawArray());
        $this->em->flush();

        return ['status' => 'refunded'];
    }
}