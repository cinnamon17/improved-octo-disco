<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    //
    public function isUserExists(int $chatId): bool
    {
        return $this->findOneBy(['chat_id' => $chatId]) !== null;
    }

    /**
     * Iterador de usuarios para broadcast, en lotes y por id.
     *
     * El lote evita cargarlo todo en memoria y el cursor de id permite
     * reanudar una ejecucion interrumpida sin volver a enviar a los que ya
     * recibieron el mensaje. Cada vuelta construye la query de nuevo: si se
     * reutilizara el query builder, las condiciones se irian acumulando.
     *
     * @return iterable<int, User>
     */
    public function iterateForBroadcast(int $batchSize = 100, ?int $afterId = null): iterable
    {
        $cursor = $afterId;

        while (true) {
            $qb = $this->createQueryBuilder('u')
                ->orderBy('u.id', 'ASC')
                ->setMaxResults($batchSize);

            if ($cursor !== null) {
                $qb->andWhere('u.id > :cursor')->setParameter('cursor', $cursor);
            }

            $batch = $qb->getQuery()->getResult();

            if ($batch === []) {
                return;
            }

            foreach ($batch as $user) {
                yield $user;
            }

            $cursor = (int) $batch[count($batch) - 1]->getId();
        }
    }
}
