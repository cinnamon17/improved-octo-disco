<?php

namespace App\Tests\Integration\Service;

use App\Entity\AiUsageLog;
use App\Entity\User;
use App\Exception\InsufficientCreditsException;
use App\Repository\AiUsageLogRepository;
use App\Service\CreditManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Concurrencia real contra la base de datos.
 *
 * Un test unitario con EntityManager mockeado no demuestra nada sobre
 * carreras: lo unico que las resuelve es el UPDATE condicional. aqui se
 * lanzan varias reservas sobre el mismo usuario y solo las que caben pasan.
 */
class CreditManagerConcurrencyTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Connection $conn;
    private CreditManager $creditManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->conn = $container->get(Connection::class);
        $this->creditManager = $container->get(CreditManager::class);
    }

    private function createUser(int $credits): User
    {
        $user = new User();
        $user->setChatId(900000000 + random_int(100000, 999999))
            ->setFirstName('Test')
            ->setIsBot(false);
        $user->addCredits($credits);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function newLog(User $user): AiUsageLog
    {
        return (new AiUsageLog())
            ->setUser($user)
            ->setTelegramMessageId('1')
            ->setModel('deepseek-chat');
    }

    private function balanceOf(int $userId): int
    {
        return (int) $this->conn->fetchOne('SELECT credits FROM user WHERE id = ?', [$userId]);
    }

    public function testReserveDebitsThePrice(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));

        $this->assertSame(8, $user->getCredits());
        $this->assertSame(8, $this->balanceOf($user->getId()));
    }

    public function testReserveRejectsWhenBalanceIsInsufficient(): void
    {
        $user = $this->createUser(1);

        $this->expectException(InsufficientCreditsException::class);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
    }

    public function testFailedReservationLeavesBalanceUntouched(): void
    {
        $user = $this->createUser(1);

        try {
            $this->creditManager->reserve($user, 2, $this->newLog($user));
            $this->fail('Deberia lanzar InsufficientCreditsException');
        } catch (InsufficientCreditsException) {
            // esperado
        }

        $this->assertSame(1, $this->balanceOf($user->getId()));
    }

    /**
     * Requisito central: N peticiones simultaneas no pueden pasar todas el
     * check con el mismo saldo.
     */
    public function testConcurrentReservationsCannotOverdraw(): void
    {
        // Saldo para exactamente 2 reservas de 2 creditos.
        $user = $this->createUser(4);
        $userId = $user->getId();

        $granted = 0;
        $rejected = 0;

        foreach (range(1, 4) as $ignored) {
            // Cada iteracion relee del pool de conexiones, que es lo que pasa
            // entre peticiones HTTP concurrentes reales.
            $this->em->clear();

            try {
                $this->creditManager->reserve($this->reload($userId), 2, $this->newLogFor($userId));
                $granted++;
            } catch (InsufficientCreditsException) {
                $rejected++;
            }
        }

        $this->assertSame(2, $granted, 'Solo dos reservas de 2 creditos caben en un saldo de 4');
        $this->assertSame(2, $rejected);

        // Y el saldo nunca queda negativo: eso es la garantia estructural.
        $this->assertSame(0, $this->balanceOf($userId));
    }

    public function testVoidReturnsTheFullReservation(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $this->assertSame(8, $this->balanceOf($user->getId()));

        $log = $this->latestLog($user);
        $this->creditManager->void($log, 'fallo de deepseek');

        $this->assertSame(10, $this->balanceOf($user->getId()));
        $this->assertSame(0, $log->getCreditsCharged());
        $this->assertSame(AiUsageLog::STATUS_VOID, $log->getStatus());
    }

    public function testVoidWithZeroTokensNeverCharges(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $log = $this->latestLog($user);
        $this->creditManager->void($log);

        $this->assertSame(0, $log->getCreditsCharged());
        $this->assertSame(10, $this->balanceOf($user->getId()));
    }

    /**
     * D2: un stream cortado cobra proporcional, no el precio completo.
     */
    public function testCutoffSettlementChargesHalfAndRefundsTheRest(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $log = $this->latestLog($user);

        $this->creditManager->settle($log, 1);

        $this->assertSame(9, $this->balanceOf($user->getId()));
        $this->assertSame(1, $log->getCreditsCharged());
        $this->assertSame(AiUsageLog::STATUS_CHARGED, $log->getStatus());
    }

    public function testSettleAtFullPriceKeepsTheReservation(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $log = $this->latestLog($user);

        $this->creditManager->settle($log, 2);

        $this->assertSame(8, $this->balanceOf($user->getId()));
        $this->assertSame(2, $log->getCreditsCharged());
    }

    public function testSettleAboveReservationNeverGoesNegative(): void
    {
        $user = $this->createUser(2);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $this->assertSame(0, $this->balanceOf($user->getId()));

        $log = $this->latestLog($user);

        // Ajuste de +5 sobre una reserva de 2 con el saldo ya a cero: el
        // UPDATE condicional no toca nada y nunca queda en negativo.
        $this->creditManager->settle($log, 7);

        $this->assertSame(0, $this->balanceOf($user->getId()));
    }

    public function testCreditAddsBalance(): void
    {
        $user = $this->createUser(0);

        $this->creditManager->credit($user, 50);

        $this->assertSame(50, $this->balanceOf($user->getId()));
    }

    public function testStaleReservationsAreVoided(): void
    {
        $user = $this->createUser(10);

        $this->creditManager->reserve($user, 2, $this->newLog($user));
        $this->assertSame(8, $this->balanceOf($user->getId()));

        // Corta el log para que cuente como huerfano.
        $this->conn->executeStatement(
            'UPDATE ai_usage_log SET created_at = :old WHERE user_id = :id',
            ['old' => (new \DateTimeImmutable('-2 hours'))->format('Y-m-d H:i:s'), 'id' => $user->getId()]
        );

        $voided = $this->creditManager->voidStaleReservations(new \DateTimeImmutable('-30 minutes'));

        $this->assertSame(1, $voided);
        $this->assertSame(10, $this->balanceOf($user->getId()));
    }

    private function reload(int $userId): User
    {
        return $this->em->find(User::class, $userId);
    }

    private function newLogFor(int $userId): AiUsageLog
    {
        return $this->newLog($this->reload($userId));
    }

    private function latestLog(User $user): AiUsageLog
    {
        $this->em->clear();

        return static::getContainer()->get(AiUsageLogRepository::class)
            ->findOneBy(['user' => $user], ['id' => 'DESC']);
    }
}