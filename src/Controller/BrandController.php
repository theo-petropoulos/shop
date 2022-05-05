<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Brand;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BrandController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Affiche toutes les marques disponibles
     *
     * @Route("/brands", name="show_brands_all")
     */
    public function showBrandsIndex(Request $request, BrandRepository $brandRepository): Response
    {
        $brands = $brandRepository->findAll();

        return $this->render('brand/show_all.html.twig', [
            'brands'    => $brands
        ]);
    }

    /**
     * Affiche une marque en particulier
     *
     * @Route("/brands/{id}", name="show_brand")
     */
    public function showBrandIndex(Request $request, Brand $brand): Response
    {

        return $this->render('brand/show.html.twig', [
            'brand'       => $brand,
        ]);
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