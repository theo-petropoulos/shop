<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Brand;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BrandController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * Affiche toutes les marques disponibles
     *
     * @Route("/brands", name="show_brands_all")
     */
    public function showBrandsIndex(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        return $this->render('brand/show_all.html.twig');
    }

    /**
     * Affiche une marque en particulier
     *
     * @Route("/brands/{brandId}", name="show_brand")
     */
    public function showBrandIndex(Request $request, int $brandId): Response
    {
        $em     = $this->doctrine->getManager();
        $brand  = $em->getRepository(Brand::class)->findOneBy(['id' => $brandId]);

        if ($brand)
        {
            return $this->render('brand/show.html.twig', [
                'brand'       => $brand,
            ]);
        }
        else throw new NotFoundHttpException('La marque demandée n\'existe pas.');
    }

    /**
     * Affiche un produit en particulier en indexant la marque pour navigation
     *
     * @Route("/brands/{brandId}/product/{productId}", name="show_brand_show_product")
     */
    public function showBrandProduct(Request $request, int $brandId, int $productId): Response
    {
        $em         = $this->doctrine->getManager();
        $brand      = $em->getRepository(Brand::class)->findOneBy(['id' => $brandId]);
        $product    = $em->getRepository(Product::class)->findOneBy(['id' => $productId]);

        if ($brand and $product) {
            return $this->render('product/show.html.twig', [
                'product'       => $product,
                'brand'         => $brand,
            ]);
        }
        else throw new NotFoundHttpException('La marque ou le produit demandé n\'existe pas.');
    }
}