<?php

namespace App\Service;

use App\Entity\AiUsageLog;
use App\Entity\StarTransaction;
use App\Entity\User;
use App\Exception\InsufficientCreditsException;
use App\Repository\AiUsageLogRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Unica clase autorizada a mutar User::$credits.
 *
 * Todas las operaciones de saldo son UN UPDATE condicional dentro de una
 * transaccion. La condicion "credits >= :c" resuelve la carrera en el motor,
 * sin ventana entre leer y escribir, y devuelve 0 filas afectadas cuando no
 * hay saldo, en cuyo caso no se ha escrito nada. Por eso el saldo negativo
 * es estructuralmente imposible.
 */
class CreditManager implements LoggerAwareInterface
{
    private Connection $conn;
    private EntityManagerInterface $em;
    private AiUsageLogRepository $usageLogRepository;
    private LoggerInterface $logger;

    public function __construct(
        Connection $conn,
        EntityManagerInterface $em,
        AiUsageLogRepository $usageLogRepository,
    ) {
        $this->conn = $conn;
        $this->em = $em;
        $this->usageLogRepository = $usageLogRepository;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Debita el saldo y deja el log en RESERVED. Es lo que hace imposible
     * que N peticiones simultaneas pasen todas el check con el mismo saldo:
     * cada una lo toma, no lo comprueba.
     */
    public function reserve(User $user, int $credits, AiUsageLog $log): AiUsageLog
    {
        return $this->atomic(function () use ($user, $credits, $log) {
            $affected = $this->conn->executeStatement(
                'UPDATE user SET credits = credits - :c WHERE id = :id AND credits >= :c',
                ['c' => $credits, 'id' => $user->getId()]
            );

            if ($affected === 0) {
                throw new InsufficientCreditsException(
                    (int) $user->getId(),
                    $credits,
                    (int) $this->conn->fetchOne('SELECT credits FROM user WHERE id = :id', ['id' => $user->getId()])
                );
            }

            // El UPDATE fue nativo: la identidad de Doctrine quedo con el
            // saldo anterior. Sin este refresh, getCredits() miente.
            $this->em->refresh($user);

            $log->setStatus(AiUsageLog::STATUS_RESERVED)->setCreditsReserved($credits);
            $this->em->persist($log);
            $this->em->flush();

            return $log;
        });
    }

    /**
     * Liquida la reserva al consumo real. Nunca deja saldo negativo: si el
     * ajuste a la baja excede lo que el usuario tiene, se queda con lo que
     * haya, que es preferible a un saldo negativo.
     */
    public function settle(AiUsageLog $log, int $actualCredits): void
    {
        $this->atomic(function () use ($log, $actualCredits) {
            $userId = (int) $log->getUser()->getId();
            // La reserva ya debito credits_reserved. Hay que ajustar esa
            // diferencia: si se cobro menos de lo reservado, devolver; si se
            // cobro mas, cobrar la diferencia.
            $delta = $actualCredits - $log->getCreditsReserved();

            if ($delta < 0) {
                // Se cobro menos de la reserva (stream cortado): devolucion
                // incondicional, siempre es seguro.
                $this->conn->executeStatement(
                    'UPDATE user SET credits = credits + :d WHERE id = :id',
                    ['d' => abs($delta), 'id' => $userId]
                );
            } elseif ($delta > 0) {
                $affected = $this->conn->executeStatement(
                    'UPDATE user SET credits = credits - :d WHERE id = :id AND credits >= :d',
                    ['d' => $delta, 'id' => $userId]
                );

                if ($affected === 0) {
                    // La respuesta al usuario ya salio. No hay nada que hacer
                    // salvo dejar constancia de la diferencia de saldo.
                    $this->logger->warning('Ajuste de cobro superior al saldo disponible', [
                        'user_id' => $userId,
                        'delta' => $delta,
                        'usage_log_id' => $log->getId(),
                    ]);
                }
            }

            $log->setStatus(AiUsageLog::STATUS_CHARGED)->setCreditsCharged($actualCredits);
            $this->em->flush();
        });
    }

    /**
     * La IA fallo o no produjo texto util: devolucion integra. Un VOID con
     * credits_charged > 0 seria un bug de contabilidad.
     */
    public function void(AiUsageLog $log, ?string $error = null): void
    {
        $this->atomic(function () use ($log, $error) {
            if ($log->getCreditsReserved() > 0) {
                $this->conn->executeStatement(
                    'UPDATE user SET credits = credits + :c WHERE id = :id',
                    ['c' => $log->getCreditsReserved(), 'id' => $log->getUser()->getId()]
                );
            }

            $log->setStatus(AiUsageLog::STATUS_VOID)
                ->setCreditsCharged(0)
                ->setErrorMessage($error !== null ? mb_substr($error, 0, 255) : null);

            $this->em->flush();
        });
    }

    /**
     * Acredita una recarga ya confirmada por Telegram.
     */
    public function credit(User $user, int $credits): void
    {
        $this->atomic(function () use ($user, $credits) {
            $this->conn->executeStatement(
                'UPDATE user SET credits = credits + :c WHERE id = :id',
                ['c' => $credits, 'id' => $user->getId()]
            );

            $this->em->refresh($user);
        });
    }

    /**
     * Debita por refunded_payment. Si el usuario ya consumio, su saldo baja y
     * la guarda de reserve() lo bloquea hasta que reponga.
     */
    public function debit(User $user, int $credits): void
    {
        $this->atomic(function () use ($user, $credits) {
            $affected = $this->conn->executeStatement(
                'UPDATE user SET credits = credits - :c WHERE id = :id AND credits >= :c',
                ['c' => $credits, 'id' => $user->getId()]
            );

            if ($affected === 0) {
                throw new InsufficientCreditsException(
                    (int) $user->getId(),
                    $credits,
                    (int) $user->getCredits()
                );
            }

            $this->em->refresh($user);
        });
    }

    public function balanceOf(User $user): int
    {
        return $user->getCredits();
    }

    public function findLog(int $id): ?AiUsageLog
    {
        return $this->usageLogRepository->find($id);
    }

    /**
     * Anula reservas huerfanas: el worker de Messenger murio entre el dispatch
     * y el consumo, y ese credito nunca se devuelve solo.
     */
    public function voidStaleReservations(\DateTimeImmutable $olderThan): int
    {
        $stale = $this->usageLogRepository->findStaleReservations($olderThan);

        foreach ($stale as $log) {
            $this->void($log, 'Reserva huerfana: el worker no la liquido');
        }

        return count($stale);
    }

    /**
     * Envoltura transaccional con un reintento ante deadlock. Con un solo
     * UPDATE condicional sobre una fila el deadlock es practicamente
     * imposible, pero el coste del reintento es cero.
     */
    private function atomic(callable $fn): mixed
    {
        try {
            return $this->conn->transactional($fn);
        } catch (DeadlockException $e) {
            $this->logger->warning('Deadlock, reintentando operacion de creditos', [
                'exception' => $e->getMessage(),
            ]);

            return $this->conn->transactional($fn);
        }
    }
}