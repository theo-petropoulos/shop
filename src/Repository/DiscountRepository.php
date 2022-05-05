<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Discount;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Discount|null find($id, $lockMode = null, $lockVersion = null)
 * @method Discount|null findOneBy(array $criteria, array $orderBy = null)
 * @method Discount[]    findAll()
 * @method Discount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discount::class);
    }

    public function findAllWithProducts()
    {
        $discounts = $this->findBy([], ['startingDate' => 'ASC']);

        foreach ($discounts as $k => $v)
        {
            $query = $this->createQueryBuilder('d')
                ->from(Product::class, 'p')
                ->addSelect('p.id')
                ->addSelect('b.name as authorName')
                ->addSelect('p.name as productName')
                ->innerJoin(Author::class, 'b')
                ->where('p.discount = :discount')
                ->andWhere('p.author = b.id')
                ->addOrderBy('authorName')
                ->addOrderBy('productName')
                ->setParameter(':discount', $v->getId())
            ;
            $results = $query->getQuery()->getResult();
            dump($results);
        }

        return $discounts;
    }

    // /**
    //  * @return Discount[] Returns an array of Discount objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Discount
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
