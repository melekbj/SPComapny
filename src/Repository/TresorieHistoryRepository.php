<?php

namespace App\Repository;

use App\Entity\TresorieHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TresorieHistory>
 *
 * @method TresorieHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TresorieHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TresorieHistory[]    findAll()
 * @method TresorieHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorieHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TresorieHistory::class);
    }

//    /**
//     * @return TresorieHistory[] Returns an array of TresorieHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TresorieHistory
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
