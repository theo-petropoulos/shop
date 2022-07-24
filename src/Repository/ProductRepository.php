<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\QueryBuilder\RandomizedFetch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\Pure;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne une quantité de produits aléatoires
     *
     * @param int|null $amount
     *
     * @return mixed
     */
    public function getRandomProducts(?int $amount): mixed
    {
        $randomQB = new RandomizedFetch($this->getEntityManager());

        return $randomQB->getRandom(Product::class, $amount);
    }

    public function findAllSortedByAuthors()
    {
       $query   = $this->createQueryBuilder('p')
                    ->addSelect('b.name as authorName')
                    ->innerJoin(Author::class, 'b')
                    ->where('b.id = p.author')
                    ->addOrderBy('b.active', 'DESC')
                    ->addOrderBy('authorName')
                    ->addOrderBy('p.id');
       $results = $query->getQuery()->getResult();

       foreach ($results as $k => $v)
           $results[$k] = $v[0];

       return $results;
    }

    /**
     * Retourne le dernier produit vendu
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getLastSoldProduct(): Product
    {
        $query  = $this->createQueryBuilder('p')
            ->innerJoin(Order::class, 'o')
            ->innerJoin(OrderDetail::class, 'od')
            ->where('od.order = o.id')
            ->andWhere('od.product = p.id')
            ->orderBy('o.purchaseDate', 'DESC')
            ->addOrderBy('od.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getSingleResult();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
