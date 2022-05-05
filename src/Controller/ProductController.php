<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct() {}

    /**
     * Affiche tous les produits disponibles
     *
     * @Route("/products", name="show_products")
     *
     * @param Request $request
     * @return Response
     */
    public function showProductsIndex(Request $request, ProductRepository $productRepository): Response
    {
        return $this->render('product/show_all.html.twig', [

        ]);
    }

    /**
     * Affiche un produit en particulier
     *
     * @Route("/products/{id}", name="show_product")
     */
    public function showProductIndex(Request $request, Product $product): Response
    {
        $author = $product->getAuthor();

        return $this->render('product/show.html.twig', [
            'product'   => $product
        ]);
    }
}
