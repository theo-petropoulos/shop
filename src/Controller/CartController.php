<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * Ajoute un produit au panier du client
     *
     * @Route("/products/{productId}/to_cart", name="add_product_to_cart")
     */
    public function addProductToCart(Request $request): Response
    {
        return $this->render('home/index.html.twig');
    }
}