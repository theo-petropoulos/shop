<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function searchProducts(string $search): mixed
    {
        $array      = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0,  PREG_SPLIT_DELIM_CAPTURE);
        $searchn    = '';
        foreach ($array as $key => $value) {
            if ($key === 0)
                $searchn    = $value;
            else
                $searchn    .= " " . $value;
        }

        $query      = $this->createQueryBuilder('p')
                        ->distinct()
                        ->addSelect('a.id as author')
                        ->addSelect('i.name as imageName')
                        ->innerJoin(Author::class, 'a')
                        ->innerJoin(Image::class, 'i')
                        ->where(
                            'p.name LIKE :searchn1 OR p.name LIKE :searchn2 OR p.name LIKE :searchn3 OR
                            a.name LIKE :searchn1 OR a.name LIKE :searchn2 OR a.name LIKE :searchn3 OR
                            p.description LIKE :searchn1 OR p.description LIKE :searchn2 OR p.description LIKE :searchn3')
                        ->andWhere('a.id = p.author')
                        ->andWhere('i.product = p.id')
                        ->orderBy(
                            'CASE 
                                    WHEN p.name LIKE :searchn1 OR p.name LIKE :searchn2 THEN 1 
                                    WHEN a.name LIKE :searchn1 OR a.name LIKE :searchn2 THEN 2
                                    WHEN p.description LIKE :searchn1 OR p.description LIKE :searchn2 THEN 3 
                                    WHEN p.name LIKE :searchn3 OR a.name LIKE :searchn3 OR p.description LIKE :searchn3 THEN 4
                                    ELSE 99
                                END')
                        ->setMaxResults(30)
                        ->setParameters(['searchn1' => "$searchn%", 'searchn2' => " $searchn%", 'searchn3' => "%$searchn%"]);

        return $query->getQuery()->getArrayResult();
    }
}
