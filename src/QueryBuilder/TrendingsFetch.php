<?php

namespace App\QueryBuilder;

use App\Entity\Author;
use App\Entity\Discount;
use App\Entity\Product;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use http\Exception\InvalidArgumentException;

class TrendingsFetch
{
    private QueryBuilder $queryBuilder;

    public function __construct($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getResults()
    {

        $query      = $this->queryBuilder
            ->select('p.somme')
            ->from ('purchases7d', 'p')
            ->setMaxResults(10)
            ->getQuery()
        ;

        return $query->getResult();
    }
}