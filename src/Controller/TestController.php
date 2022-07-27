<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\QueryBuilder\AdminSearch;
use App\Repository\AddressRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    # Tests
    #[Route(path: '/tests', name: 'tests')]
    public function testIndex(Request $request, UserRepository $userRepository, AddressRepository $addressRepository, ProductRepository $productRepository): Response
    {
        $search             = 'to';

        $qb         = $this->entityManager->createQueryBuilder();
        $qbSearch   = new AdminSearch($qb, 'user', $search);
        $searchedProducts   = $productRepository->searchProducts($search);

        $results    = $qbSearch->getResults();

        dd($results);
    }
}