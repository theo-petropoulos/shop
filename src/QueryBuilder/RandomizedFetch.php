<?php

namespace App\QueryBuilder;

use App\Entity\Author;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class RandomizedFetch
{
    private QueryBuilder $queryBuilder;

    public function __construct(EntityManagerInterface $em)
    {
        $this->queryBuilder = new QueryBuilder($em);
    }

    /**
     * Retourne une sélection aléatoire
     *
     * @param string $class
     * @param int $quantity
     *
     * @return mixed
     */
    public function getRandom(string $class, int $quantity): mixed
    {
        $query = $this->queryBuilder
            ->select('e')
            ->from($class, 'e')
            ->where('e.active = 1');

        if ($class === Author::class)
            $this->queryBuilder
                ->innerJoin(Product::class, 'p')
                ->andWhere('p.author = e');

        $this->queryBuilder
            ->orderBy('RAND()')
            ->setMaxResults($quantity);

        return $query->getQuery()->getResult();
    }
}