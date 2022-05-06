<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct() {}

    /**
     * Affiche le panier
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/cart/", name="show_cart")
     */
    public function showCart(Request $request, ProductRepository $productRepository): Response
    {
        $arrayCart  = json_decode($request->cookies->get('cart'), true);
        $cart       = [];

        foreach($arrayCart as $productId => $quantity) {
            $cart[$quantity] = $productRepository->findOneBy(['id' => $productId]);
        }

        return $this->render('cart/show.html.twig', [
            'cart'      => $cart
        ]);
    }

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