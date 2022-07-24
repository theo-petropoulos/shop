<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct() {}

    # Recherche globale
    #[Route(path: '/search', name: 'user_global_search')]
    public function search(Request $request, ProductRepository $productRepository, AuthorRepository $authorRepository): Response
    {
        $search             = $request->get('search');

        $searchedProducts   = $productRepository->searchProducts($search);

        return new JsonResponse(['search' => $searchedProducts]);
    }
}