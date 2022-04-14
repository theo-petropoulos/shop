<?php

namespace App\QueryBuilder;

use App\Entity\Brand;
use App\Entity\Discount;
use App\Entity\Product;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use http\Exception\InvalidArgumentException;

class AdminSearch
{
    private QueryBuilder $queryBuilder;
    private string $entity;
    private string $search;

    public function __construct($queryBuilder, $entity, $search)
    {
        $this->queryBuilder = $queryBuilder;
        $this->entity       = $entity;
        $this->search       = $search;
    }

    public function getResults()
    {
        $search1    = "% $this->search%";
        $search2    = "$this->search%";

        $query      = match ($this->entity) {
            'product'   => $this->queryBuilder
                ->select('p.id, b.name AS brand_name, p.name AS product_name, p.description, p.price, p.stock, p.active')
                ->distinct()
                ->from(Product::class, 'p')
                ->innerJoin(Brand::class, 'b', Join::WITH, 'b.id = p.brand')
                ->where('( b.name LIKE :search1 ) OR ( b.name LIKE :search2 ) OR ( p.name LIKE :search1 ) OR ( p.name LIKE :search2 )')
                ->orderBy('product_name, brand_name')
                ->getQuery(),
            'brand'     => $this->queryBuilder
                ->select('b.id, b.name as brand_name, b.description, b.active')
                ->from(Brand::class, 'b')
                ->where('b.name LIKE :search1 OR b.name LIKE :search2')
                ->orderBy('b.name', 'ASC')
                ->getQuery(),
            'discount'  => $this->queryBuilder
                ->select('d.id, d.name AS discount_name, p.name AS product_name, b.name AS brand_name, d.percentage, d.startingDate, d.endingDate')
                ->from(Discount::class, 'd')
                ->innerJoin(Product::class, 'p', Join::WITH, 'p.discount = d.id')
                ->innerJoin(Brand::class, 'b', Join::WITH, 'b.id = p.brand')
                ->where('d.name LIKE :search1 OR d.name LIKE :search2 OR p.name LIKE :search1 OR p.name LIKE :search2')
                ->orderBy('d.name, p.name')
                ->getQuery(),
            default     => throw new InvalidArgumentException(),
        };

        $query->setParameters([
            'search1' => $search1,
            'search2' => $search2
        ]);

        return $query->getResult();
    }
}