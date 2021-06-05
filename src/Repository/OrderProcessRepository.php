<?php

namespace App\Repository;

use App\Entity\OrderProcess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderProcess|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProcess|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProcess[]    findAll()
 * @method OrderProcess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProcess::class);
    }

    // /**
    //  * @return OrderProcess[] Returns an array of OrderProcess objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderProcess
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
