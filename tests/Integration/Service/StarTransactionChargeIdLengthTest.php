<?php

namespace App\Tests\Integration\Service;

use App\Entity\StarTransaction;
use App\Entity\User;
use App\Repository\StarTransactionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Regresion del incidente real: un pago de 10 Stars llego a successful_payment
 * pero revento al persistir porque telegram_payment_charge_id mide ~110
 * caracteres y la columna era VARCHAR(64):
 *
 *   SQLSTATE[22001]: Data too long for column 'telegram_charge_id'
 *
 * El pago se perdio porque el rollback se llevo por delante el credito. Este
 * test usa el charge id real del incidente para que la columna no vuelva a
 * quedarse corta.
 */
class StarTransactionChargeIdLengthTest extends KernelTestCase
{
    /** Charge id real del log del incidente: 110 caracteres. */
    private const REAL_CHARGE_ID = 'stxA3vodMqdiwHMAT6iSnv4f9nX0upWG71kAwHSe05ElrL4VuNBKddRWRmNaXx0-bgIC6-apU-bskdUbvSOVW4EE8YmTavaSodsyMdcrw6OQKw';

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

    public function testColumnAcceptsTheRealStarsChargeId(): void
    {
        $user = $this->createUser();
        $payload = strtoupper(bin2hex(random_bytes(8)));

        $transaction = (new StarTransaction())
            ->setUser($user)
            ->setInvoicePayload($payload)
            ->setTelegramChargeId(self::REAL_CHARGE_ID)
            ->setCurrency('XTR')
            ->setTotalAmount(10)
            ->setCredits(10)
            ->setStatus(StarTransaction::STATUS_COMPLETED)
            ->setProviderChargeId('1136298813_1');

        // Este flush es el que reventaba en produccion.
        $this->em->persist($transaction);
        $this->em->flush();

        $this->em->clear();

        $persisted = $this->repository->findByChargeId(self::REAL_CHARGE_ID);

        $this->assertNotNull($persisted, 'El charge id real de Stars debe persistir sin truncarse');
        $this->assertSame(self::REAL_CHARGE_ID, $persisted->getTelegramChargeId());
    }

    public function testRealChargeIdIsLongerThanTheOldColumnWidth(): void
    {
        // Documenta por que 64 era insuficiente: si esto deja de ser cierto,
        // el test de arriba es el que avisa.
        $this->assertGreaterThan(64, strlen(self::REAL_CHARGE_ID));
    }

    public function testColumnIsDeclaredWideEnoughForIt(): void
    {
        $length = (int) $this->conn->fetchOne(
            "SELECT CHARACTER_MAXIMUM_LENGTH
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'star_transaction'
               AND COLUMN_NAME = 'telegram_charge_id'"
        );

        $this->assertGreaterThanOrEqual(
            strlen(self::REAL_CHARGE_ID),
            $length,
            'La columna debe caber el charge id real de Stars'
        );
    }

    public function testUniqueIndexStillEnforcesIdempotencyWithLongIds(): void
    {
        $user = $this->createUser();

        $this->persistCompleted($user, self::REAL_CHARGE_ID, strtoupper(bin2hex(random_bytes(8))));

        // La idempotencia contra pagos duplicados depende de este UNIQUE.
        // Con el token largo tiene que seguir rechazando el duplicado.
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        $this->persistCompleted($user, self::REAL_CHARGE_ID, strtoupper(bin2hex(random_bytes(8))));
    }

    private function persistCompleted(User $user, string $chargeId, string $payload): StarTransaction
    {
        $transaction = (new StarTransaction())
            ->setUser($user)
            ->setInvoicePayload($payload)
            ->setTelegramChargeId($chargeId)
            ->setCurrency('XTR')
            ->setTotalAmount(10)
            ->setCredits(10)
            ->setStatus(StarTransaction::STATUS_COMPLETED);

        $this->em->persist($transaction);
        $this->em->flush();

        return $transaction;
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setChatId(600000000 + random_int(100000, 999999))
            ->setFirstName('Test')
            ->setIsBot(false);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}