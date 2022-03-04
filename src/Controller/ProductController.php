<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * Affiche tous les produits disponibles
     *
     * @Route("/products", name="show_products_all")
     */
    public function showProductsIndex(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        return $this->render('product/show_all.html.twig');
    }

    /**
     * Affiche un produit en particulier
     *
     * @Route("/products/{productId}", name="show_product")
     */
    public function showProductIndex(Request $request, int $productId): RedirectResponse
    {
        $em         = $this->doctrine->getManager();
        $product    = $em->getRepository(Product::class)->findOneBy(['id' => $productId]);

        if ($product)
        {
            $brand = $product->getBrand();
            return $this->redirectToRoute("show_brand_show_product", ['brandId' => $brand->getid(), 'productId' => $product->getId()]);
        }
        else throw new NotFoundHttpException('Le produit demandé n\'existe pas.');
    }
}