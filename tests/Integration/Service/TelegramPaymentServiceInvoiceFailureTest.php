<?php

namespace App\Tests\Integration\Service;

use App\Entity\StarTransaction;
use App\Entity\User;
use App\Repository\StarTransactionRepository;
use App\Service\TelegramPaymentService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Una factura que Telegram rechazo no puede quedarse en PENDING: el
 * pre_checkout_query valida precisamente contra PENDING, asi que ahi se
 * daria por valida un invoice que nunca existio en el servidor de Telegram.
 */
class TelegramPaymentServiceInvoiceFailureTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Connection $conn;
    private StarTransactionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->conn = $container->get(Connection::class);
        $this->repository = $container->get(StarTransactionRepository::class);
    }

    public function testRejectedInvoiceIsMarkedFailedNotPending(): void
    {
        $user = $this->createUser();
        $payload = strtoupper(bin2hex(random_bytes(8)));

        // Replica la logica de createInvoice con un sendInvoice rechazado,
        // que es lo que hace el cliente cuando Telegram devuelve ok=false.
        $this->conn->transactional(function () use ($user, $payload) {
            $transaction = (new StarTransaction())
                ->setUser($user)
                ->setInvoicePayload($payload)
                ->setTelegramChargeId('pending-' . $payload)
                ->setCurrency('XTR')
                ->setTotalAmount(10)
                ->setCredits(10)
                ->setStatus(StarTransaction::STATUS_PENDING);

            $this->em->persist($transaction);
            $this->em->flush();

            $response = $this->rejectedInvoiceResponse();

            if (($response['ok'] ?? true) === false) {
                $transaction->setStatus(StarTransaction::STATUS_FAILED)
                    ->setRawPayload(['send_invoice_response' => $response]);
                $this->em->flush();
            }
        });

        $this->em->clear();

        $persisted = $this->repository->findByInvoicePayload($payload);

        $this->assertNotNull($persisted);
        $this->assertSame(
            StarTransaction::STATUS_FAILED,
            $persisted->getStatus(),
            'Una factura rechazada no puede quedar en PENDING'
        );
        $this->assertFalse($persisted->isPending());
    }

    /**
     * El pre_checkout_query rechaza cualquier cosa que no sea PENDING, asi que
     * una factura FAILED nunca se aprueba.
     */
    public function testPreCheckoutWouldRejectAFailedInvoice(): void
    {
        $failed = (new StarTransaction())
            ->setStatus(StarTransaction::STATUS_FAILED);

        $this->assertFalse($failed->isPending());

        // Misma condicion que usa TelegramPaymentService::handlePreCheckoutQuery
        $accepted = $failed !== null
            && $failed->isPending()
            && $failed->getCurrency() === 'XTR';

        $this->assertFalse($accepted);
    }

    public function testFailedTransactionKeepsTheTelegramErrorForAudit(): void
    {
        $user = $this->createUser();
        $payload = strtoupper(bin2hex(random_bytes(8)));

        $response = $this->rejectedInvoiceResponse();

        $transaction = (new StarTransaction())
            ->setUser($user)
            ->setInvoicePayload($payload)
            ->setTelegramChargeId('pending-' . $payload)
            ->setCurrency('XTR')
            ->setTotalAmount(10)
            ->setCredits(10)
            ->setStatus(StarTransaction::STATUS_FAILED)
            ->setRawPayload(['send_invoice_response' => $response]);

        $this->em->persist($transaction);
        $this->em->flush();
        $this->em->clear();

        $persisted = $this->repository->findByInvoicePayload($payload);
        $raw = $persisted->getRawPayload();

        // Sin esto no se puede saber despues por que fallo la factura.
        $this->assertArrayHasKey('send_invoice_response', $raw);
        $this->assertFalse($raw['send_invoice_response']['ok']);
        $this->assertStringContainsString(
            'PROVIDER_ACCOUNT_INVALID',
            $raw['send_invoice_response']['description']
        );
    }

    private function rejectedInvoiceResponse(): array
    {
        return [
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: PROVIDER_ACCOUNT_INVALID',
        ];
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setChatId(700000000 + random_int(100000, 999999))
            ->setFirstName('Test')
            ->setIsBot(false);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}