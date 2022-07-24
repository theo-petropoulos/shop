<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Product;
use App\Repository\AuthorRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct() {}

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    # Accueil
    #[Route(path: '/', name: 'home')]
    public function index(ProductRepository $productRepository, AuthorRepository $authorRepository): Response
    {
        /** @var Author|null $author */
        $author             = $authorRepository->getRandomAuthors(1)[0];
        /** @var Product|null $product */
        $product            = $productRepository->getRandomProducts(1)[0];
        /** @var Product|null $lastSoldProduct */
        $lastSoldProduct    = $productRepository->getLastSoldProduct();

        return $this->render('home/index.html.twig', [
            'author'            => $author,
            'product'           => $product,
            'lastSoldProduct'   => $lastSoldProduct
        ]);
    }
}