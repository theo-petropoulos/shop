<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Discount;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\DiscountRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Pure]
    public function __construct(private ManagerRegistry $doctrine) {}

    # Accueil Admin
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/', name: 'admin')]
    public function adminIndex(Request $request): Response
    {
        return $this->render('admin/show.html.twig');
    }

    # Mot de passe Admin
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/password', name: 'admin_edit_password')]
    public function adminEditPassword(Request $request): Response
    {
        return $this->render('admin/includes/edit_password.html.twig');
    }

    # Administration des clients
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/customers', name: 'admin_show_customers')]
    public function adminShowCustomers(Request $request): Response
    {
        $em         = $this->doctrine->getManager();
        $users      = $em->getRepository(User::class)->findAll();
        foreach ($users as $k => $user)
            if (in_array('ROLE_ADMIN', $user->getRoles()))
                unset($users[$k]);

        return $this->render('admin/includes/show_customers.html.twig', [
            'users'         => $users,
        ]);
    }

    # Administration des produits
    #[NoReturn]
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products', name: 'admin_show_products')]
    public function adminShowProducts(Request $request, BrandRepository $brandRepository, ProductRepository $productRepository, DiscountRepository $discountRepository): Response
    {
        $brands     = $brandRepository->findBy([], ['active' => 'DESC']);
        $products   = $productRepository->findAllSortedByBrands();
        $discounts  = $discountRepository->findAllWithProducts();

        dd($products);

        return $this->render('admin/includes/show_products.html.twig', [
            'brands'        => $brands,
            'products'      => $products,
            'discounts'     => $discounts
        ]);
    }

    # Administration des produits
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins', name: 'admin_show_admins')]
    public function adminShowAdmins(Request $request): Response
    {
        return $this->render('admin/includes/show_admins.html.twig');
    }

}