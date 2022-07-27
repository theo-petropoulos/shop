<?php

namespace App\QueryBuilder;

use App\Entity\Address;
use App\Entity\Author;
use App\Entity\Discount;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
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
                ->select('p.id, b.name AS author_name, p.name AS product_name, p.description, p.price, p.stock, p.active')
                ->distinct()
                ->from(Product::class, 'p')
                ->innerJoin(Author::class, 'b', Join::WITH, 'b.id = p.author')
                ->where('( b.name LIKE :search1 ) OR ( b.name LIKE :search2 ) OR ( p.name LIKE :search1 ) OR ( p.name LIKE :search2 )')
                ->orderBy('product_name, author_name')
                ->getQuery(),
            'author'     => $this->queryBuilder
                ->select('b.id, b.name as author_name, b.description, b.active')
                ->from(Author::class, 'b')
                ->where('b.name LIKE :search1 OR b.name LIKE :search2')
                ->orderBy('b.name', 'ASC')
                ->getQuery(),
            'discount'  => $this->queryBuilder
                ->select('d.id, d.name AS discount_name, p.name AS product_name, b.name AS author_name, d.percentage, d.startingDate, d.endingDate')
                ->from(Discount::class, 'd')
                ->innerJoin(Product::class, 'p', Join::WITH, 'p.discount = d.id')
                ->innerJoin(Author::class, 'b', Join::WITH, 'b.id = p.author')
                ->where('d.name LIKE :search1 OR d.name LIKE :search2 OR p.name LIKE :search1 OR p.name LIKE :search2')
                ->orderBy('d.name, p.name')
                ->getQuery(),
            'user'      => $this->queryBuilder
                ->select('u.id, u.email, u.lastName, u.firstName, u.isVerified')
                ->from(User::class, 'u')
                ->where('u.lastName LIKE :search1 OR u.firstName LIKE :search1 OR u.lastName LIKE :search2 OR u.firstName LIKE :search2 OR u.id LIKE :search1 OR u.id LIKE :search2')
                ->andWhere('JSON_CONTAINS(u.roles, \'"ROLE_ADMIN"\') = 0')
                ->orderBy('u.id')
                ->getQuery(),
            'order'     => $this->queryBuilder
                ->select('o.id, o.status, o.trackingNumber')
                ->from(Order::class, 'o')
                ->innerJoin(User::class, 'u')
                ->where('o.id LIKE :search1 OR o.id LIKE :search2 OR o.trackingNumber LIKE :search1 OR o.trackingNumber LIKE :search2 OR u.email LIKE :search1 OR u.email LIKE :search2')
                ->andWhere('o.customer = u.id')
                ->orderBy('o.id')
                ->getQuery(),
            'address'   => $this->queryBuilder
                ->select('a.id, a.lastName, a.firstName, a.streetNumber, a.streetName, a.streetAddition, a.postalCode, a.city')
                ->from(Address::class, 'a')
                ->innerJoin(User::class, 'u')
                ->where('a.id LIKE :search1 OR a.id LIKE :search2 OR a.lastName LIKE :search1 OR a.lastName LIKE :search2 OR a.streetName LIKE :search1 OR a.streetName LIKE :search2 OR u.email LIKE :search1 OR u.email LIKE :search2')
                ->andWhere('a.customer = u.id')
                ->orderBy('a.id')
                ->getQuery(),
            default     => throw new InvalidArgumentException()
        };

        $query->setParameters([
            'search1' => $search1,
            'search2' => $search2
        ]);

        return $query->getResult();
    }
}