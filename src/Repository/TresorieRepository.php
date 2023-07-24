<?php

namespace App\Repository;

use App\Entity\Tresorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tresorie>
 *
 * @method Tresorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tresorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tresorie[]    findAll()
 * @method Tresorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tresorie::class);
    }

//    /**
//     * @return Tresorie[] Returns an array of Tresorie objects
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

//    public function findOneBySomeField($value): ?Tresorie
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
