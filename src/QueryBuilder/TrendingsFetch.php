<?php

namespace App\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

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