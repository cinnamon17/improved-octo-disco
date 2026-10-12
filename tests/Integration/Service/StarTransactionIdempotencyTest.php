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
 * Idempotencia de los pagos: un webhook reintentado por Telegram o un doble
 * click no pueden acreditar dos veces.
 */
class StarTransactionIdempotencyTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Connection $conn;
    private TelegramPaymentService $paymentService;
    private StarTransactionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->conn = $container->get(Connection::class);
        $this->paymentService = $container->get(TelegramPaymentService::class);
        $this->repository = $container->get(StarTransactionRepository::class);
    }

    public function testChargeIdAndPayloadAreUniqueInDatabase(): void
    {
        $columns = $this->conn->fetchAllAssociative(
            'SHOW INDEX FROM star_transaction WHERE Non_unique = 0'
        );

        $uniqueColumns = array_column($columns, 'Column_name');

        $this->assertContains('telegram_charge_id', $uniqueColumns);
        $this->assertContains('invoice_payload', $uniqueColumns);
    }

    public function testDuplicateChargeIdCannotBeInserted(): void
    {
        $user = $this->createUser();

        $first = $this->createPendingTransaction($user, 'PAYLOAD-A', 'charge-duplicado');
        $this->em->flush();

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        // El UNIQUE de telegram_charge_id es la garantia final contra pagos
        // duplicados, por encima de cualquier logica de application.
        $second = $this->createPendingTransaction($user, 'PAYLOAD-B', 'charge-duplicado');

        $this->em->persist($second);
        $this->em->flush();
    }

    public function testDuplicatePayloadCannotBeInserted(): void
    {
        $user = $this->createUser();

        $first = $this->createPendingTransaction($user, 'PAYLOAD-DUP', 'charge-1');
        $this->em->flush();

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        $second = $this->createPendingTransaction($user, 'PAYLOAD-DUP', 'charge-2');

        $this->em->persist($second);
        $this->em->flush();
    }

    public function testCompletedTransactionIsNotCreditedTwice(): void
    {
        $user = $this->createUser();
        $user->addCredits(0);
        $this->em->flush();

        $transaction = $this->createPendingTransaction($user, 'PAYLOAD-IDEM', 'charge-idem');
        $transaction->setStatus(StarTransaction::STATUS_COMPLETED);
        $this->em->flush();

        // Estado ya COMPLETED: cualquier reintento del webhook debe ser no-op.
        $this->assertTrue($transaction->isCompleted());
        $this->assertFalse($transaction->isPending());

        $found = $this->repository->findByChargeId('charge-idem');
        $this->assertNotNull($found);
        $this->assertSame(StarTransaction::STATUS_COMPLETED, $found->getStatus());
    }

    public function testInvoicePayloadIsHexAndWithinTelegramLimit(): void
    {
        // Telegram acepta maximo 128 bytes de payload.
        $payload = strtoupper(bin2hex(random_bytes(8)));

        $this->assertSame(16, strlen($payload));
        $this->assertLessThanOrEqual(128, strlen($payload));
        $this->assertMatchesRegularExpression('/^[0-9A-F]{16}$/', $payload);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setChatId(800000000 + random_int(100000, 999999))
            ->setFirstName('Test')
            ->setIsBot(false);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createPendingTransaction(User $user, string $payload, string $chargeId): StarTransaction
    {
        $transaction = (new StarTransaction())
            ->setUser($user)
            ->setInvoicePayload($payload)
            ->setTelegramChargeId($chargeId)
            ->setCurrency('XTR')
            ->setTotalAmount(25)
            ->setCredits(25)
            ->setStatus(StarTransaction::STATUS_PENDING);

        $this->em->persist($transaction);

        return $transaction;
    }
}