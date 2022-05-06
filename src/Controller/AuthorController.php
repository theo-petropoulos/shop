<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    public function __construct(){}

    /**
     * Affiche toutes les auteurs disponibles
     *
     * @Route("/authors", name="show_authors")
     */
    public function showAuthorsIndex(Request $request, AuthorRepository $authorRepository, ProductRepository $productRepository): Response
    {
        $authors = $authorRepository->findBy(['active' => true]);

        return $this->render('author/show_all.html.twig', [
            'authors'    => $authors
        ]);
    }

    /**
     * Affiche un auteur en particulier
     *
     * @Route("/authors/{id}", name="show_author")
     */
    public function showAuthorIndex(Request $request, Author $author, ProductRepository $productRepository): Response
    {
        $criteria = new Criteria();
        $criteria
            ->where(Criteria::expr()->gte('stock', 1))
            ->andWhere(Criteria::expr()->eq('author', $author))
            ->andWhere(Criteria::expr()->eq('active', true));

        $products = $productRepository->matching($criteria);

        return $this->render('author/show.html.twig', [
            'author'        => $author,
            'products'      => $products
        ]);
    }
}
