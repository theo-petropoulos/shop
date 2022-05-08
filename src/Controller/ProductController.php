<?php

namespace App\Controller;

use App\Entity\Product;
use App\QueryBuilder\TrendingsFetch;
use App\Repository\ProductRepository;
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
    # Affiche un produit en particulier
    #[Route(path: '/products/{id}', name: 'show_product')]
    public function showProduct(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $author     = $product->getAuthor();
        $topSells   = $productRepository->findBy([], ['purchases' => 'DESC'], 8);
        $conn       = $this->entityManager->getConnection();
        $sql        = 'SELECT p.* FROM `purchases7d` p LIMIT 10';
        $stmt       = $conn->executeQuery($sql);

        $trendings  = $stmt->fetchAllAssociative();

        return $this->render('product/show.html.twig', [
            'product'   => $product,
            'author'    => $author,
            'topSells'  => $topSells,
            'trendings' => $topSells
        ]);
    }
}
