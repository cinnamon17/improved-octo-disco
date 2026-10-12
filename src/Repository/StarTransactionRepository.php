<?php

namespace App\Repository;

use App\Entity\StarTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StarTransaction>
 *
 * @method StarTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method StarTransaction|null findOneBy(array $criteria, $orderBy = null)
 * @method StarTransaction[]    findAll()
 * @method StarTransaction[]    findBy(array $criteria, $orderBy = null, $limit = null, $offset = null)
 */
class StarTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StarTransaction::class);
    }

    public function findByInvoicePayload(string $payload): ?StarTransaction
    {
        return $this->findOneBy(['invoice_payload' => $payload]);
    }

    public function findByChargeId(string $chargeId): ?StarTransaction
    {
        return $this->findOneBy(['telegram_charge_id' => $chargeId]);
    }
}