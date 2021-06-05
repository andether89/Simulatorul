<?php

namespace App\Repository;

use App\Entity\OrderNumber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderNumber|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderNumber|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderNumber[]    findAll()
 * @method OrderNumber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderNumberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderNumber::class);
    }

    public function findLast()
    {
        return $this->createQueryBuilder('on')
            ->orderBy('on.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return OrderNumber[] Returns an array of OrderNumber objects
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
    public function findOneBySomeField($value): ?OrderNumber
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
