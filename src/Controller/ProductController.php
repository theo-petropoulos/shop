<?php

namespace App\Controller;

use App\Entity\Product;
use App\QueryBuilder\TrendingsFetch;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    # Affiche tous les produits disponibles
    #[Route(path: '/products', name: 'show_products')]
    public function showProductsIndex(Request $request, ProductRepository $productRepository): Response
    {
        return $this->render('product/show_all.html.twig', [

        ]);
    }

    /**
     * @throws Exception
     */
    # Affiche un produit en particulier et les suggestions
    #[Route(path: '/products/{id}', name: 'show_product')]
    public function showProduct(Product $product, ProductRepository $productRepository, Connection $connection): Response
    {
        $author         = $product->getAuthor();
        $authorProducts = $productRepository->findBy(['author' => $author], ['purchases' => 'DESC'], 10);
        shuffle($authorProducts);

        $topSells       = $productRepository->findBy([], ['purchases' => 'DESC'], 10);
        shuffle($topSells);

        $sql            = 'SELECT p.id FROM `purchases7d` p LIMIT 10';
        $stmt           = $connection->executeQuery($sql);
        $trendings      = $stmt->fetchAllAssociative();
        shuffle($trendings);

        foreach ($trendings as $key => $id) {
            $trendings[$key] = $productRepository->find($id);
        }

        return $this->render('product/show.html.twig', [
            'product'           => $product,
            'author'            => $author,
            'topSells'          => $topSells,
            'trendings'         => $trendings,
            'authorProducts'    => $authorProducts
        ]);
    }
}
