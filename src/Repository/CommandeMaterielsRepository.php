<?php

namespace App\Repository;

use App\Entity\CommandeMateriels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeMateriels>
 *
 * @method CommandeMateriels|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeMateriels|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeMateriels[]    findAll()
 * @method CommandeMateriels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeMaterielsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeMateriels::class);
    }

//    /**
//     * @return CommandeMateriels[] Returns an array of CommandeMateriels objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CommandeMateriels
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
