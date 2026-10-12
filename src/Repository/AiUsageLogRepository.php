<?php

namespace App\Repository;

use App\Entity\AiUsageLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiUsageLog>
 *
 * @method AiUsageLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiUsageLog|null findOneBy(array $criteria, $orderBy = null)
 * @method AiUsageLog[]    findAll()
 * @method AiUsageLog[]    findBy(array $criteria, $orderBy = null, $limit = null, $offset = null)
 */
class AiUsageLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiUsageLog::class);
    }

    /**
     * Reservas que quedaron huérfanas porque el worker de Messenger murio
     * entre el dispatch y el consumo. El comando de mantenimiento las anula.
     */
    public function findStaleReservations(\DateTimeImmutable $olderThan): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.status = :status')
            ->andWhere('l.created_at < :threshold')
            ->setParameter('status', AiUsageLog::STATUS_RESERVED)
            ->setParameter('threshold', $olderThan)
            ->getQuery()
            ->getResult();
    }
}