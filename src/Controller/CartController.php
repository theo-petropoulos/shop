<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct() {}

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    # Affiche le panier
    #[Route(path: '/cart', name: 'show_cart')]
    public function showCart(Request $request, ProductRepository $productRepository, Connection $connection): Response
    {
        $arrayCart  = json_decode($request->cookies->get('cart'), true);
        $cart       = [];
        $trendings  = [];

        // todo : Modifier l'association objet => quantité
        foreach ((array) $arrayCart as $productId => $quantity) {
            $cart[] = ['product' => $productRepository->findOneBy(['id' => $productId]), 'quantity' => $quantity];
        }

        if (count($cart) < 4) {
            $sql            = 'SELECT p.id FROM `purchases7d` p LIMIT 10';
            $stmt           = $connection->executeQuery($sql);
            $trendings      = $stmt->fetchAllAssociative();
            shuffle($trendings);

            foreach ($trendings as $key => $id) {
                $trendings[$key] = $productRepository->find($id);
            }
        }

        return $this->render('cart/show.html.twig', [
            'cart'      => $cart,
            'trendings' => $trendings
        ]);
    }
}