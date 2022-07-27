<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CartController extends AbstractController
{
    public function __construct() {}

    /**
     * @throws Exception
     */
    # Affiche le panier
    #[Route(path: '/cart', name: 'show_cart')]
    public function showCart(Request $request, ProductRepository $productRepository, Connection $connection, Security $security): Response
    {
        $user       = $security->getUser();
        $trendings  = [];
        $arrayCart  = json_decode($request->cookies->get('cart'), true);

        $cart       = new Cart($productRepository);
        $cart->getCartFromCookie((array) $arrayCart);

        // Affiche les suggestions si moins de 4 articles dans le panier
        if (count($cart->getCart()) < 4) {
            $sql        = 'SELECT p.id FROM `purchases7d` p LIMIT 10';
            $stmt       = $connection->executeQuery($sql);
            $trendings  = $stmt->fetchAllAssociative();
            shuffle($trendings);

            foreach ($trendings as $key => $id)
                $trendings[$key] = $productRepository->find($id);
        }

        return $this->render('cart/show.html.twig', [
            'cart'          => $cart->getCart(),
            'trendings'     => $trendings,
            'totalPrice'    => $cart->getTotalPrice(),
            'sumProducts'   => $cart->getItemCount(),
            'user'          => $user
        ]);
    }
}
