<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    public function __construct() {}

    # Affiche tous les produits disponibles
    #[Route(path: '/products', name: 'show_products')]
    public function showProductsIndex(ProductRepository $productRepository): Response
    {
        $criteria = new Criteria();
        $criteria
            ->where(Criteria::expr()->gte('stock', 1))
            ->andWhere(Criteria::expr()->eq('active', true))
            ->orderBy(['id' => 'DESC'])
            ->setMaxResults(19);

        $products = $productRepository->matching($criteria);

        return $this->render('product/show_all.html.twig', [
            'products'  => $products
        ]);
    }

    # Charge plus de produits
    #[Route(path: '/products/more/', name: 'load_products')]
    public function loadProducts(Request $request, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $counter        = $request->get('counter');

        $criteria       = new Criteria();
        $criteria
            ->where(Criteria::expr()->gte('stock', 1))
            ->andWhere(Criteria::expr()->eq('active', true))
            ->orderBy(['id' => 'DESC'])
            ->setMaxResults(20)
            ->setFirstResult(19 + 20 * $counter);

        $moreProducts   = $productRepository->matching($criteria)->toArray();

        foreach ($moreProducts as $key => $product) {
            $moreProducts[$key] = $serializer->serialize($product, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
        }

        $criteria2      = new Criteria();
        $criteria2
            ->where(Criteria::expr()->gte('stock', 1))
            ->andWhere(Criteria::expr()->eq('active', true));

        if ((19 + 20 * ($counter + 1)) >= count($productRepository->matching($criteria2)))
            $moreProducts[] = 'catalogsEnd';

        return new JsonResponse(json_encode($moreProducts));
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

    # Affiche un produit en particulier et les suggestions
    #[Route(path: '/products/{id}/{quantity}', name: 'is_available')]
    public function isAvailableAtQuantity(Product $product, int $quantity): JsonResponse
    {
        return new JsonResponse(json_encode($product->getStock() >= $quantity));
    }
}
