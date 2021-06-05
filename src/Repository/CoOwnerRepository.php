<?php

namespace App\Repository;

use App\Entity\CoOwner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoOwner|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoOwner|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoOwner[]    findAll()
 * @method CoOwner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoOwnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoOwner::class);
    }

    // /**
    //  * @return CoOwner[] Returns an array of CoOwner objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CoOwner
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
