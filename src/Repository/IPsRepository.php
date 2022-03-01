<?php

namespace App\Repository;

use App\Entity\IPs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IPs|null find($id, $lockMode = null, $lockVersion = null)
 * @method IPs|null findOneBy(array $criteria, array $orderBy = null)
 * @method IPs[]    findAll()
 * @method IPs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IPsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IPs::class);
    }

    // /**
    //  * @return IPs[] Returns an array of IPs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IPs
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
