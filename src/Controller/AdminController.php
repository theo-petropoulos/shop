<?php

namespace App\Controller;

use App\Entity\Discount;
use App\QueryBuilder\AdminSearch;
use App\Repository\BrandRepository;
use App\Repository\DiscountRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use http\Exception\InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private EntityManagerInterface $em;

    #[Pure]
    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products', name: 'admin_show_products')]
    public function adminShowProducts(Request $request, BrandRepository $brandRepository, ProductRepository $productRepository, DiscountRepository $discountRepository): Response
    {
        $brands     = $brandRepository->findBy([], ['active' => 'DESC']);
        $products   = $productRepository->findBy([], ['active' => 'DESC']);
        $discounts  = $discountRepository->findBy([], ['startingDate' => 'ASC']);

        return $this->render('admin/includes/show_products.html.twig', [
            'brands'        => $brands,
            'products'      => $products,
            'discounts'     => $discounts
        ]);
    }

    # Recherche de l'Administration des produits
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/search', name: 'admin_product_search')]
    public function adminProductsSearch(Request $request): JsonResponse
    {
        $search     = $request->get('search');
        $entity     = $request->get('table');

        $qb         = $this->em->createQueryBuilder();
        $qbSearch   = new AdminSearch($qb, $entity, $search);

        $results    = $qbSearch->getResults();

        return new JsonResponse(json_encode($results));
    }

    # Suppression d'une promotion
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/discount/delete/{id}', name: 'admin_delete_discount')]
    public function adminDeleteDiscount(Request $request, Discount $discount)
    {

    }

    # Administration des produits
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins', name: 'admin_show_admins')]
    public function adminShowAdmins(Request $request): Response
    {
        return $this->render('admin/includes/show_admins.html.twig');
    }

}