<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Discount;
use App\Entity\Image;
use App\Entity\Product;
use App\Exceptions\InvalidSizeException;
use App\Form\Admin\AddBrandType;
use App\Form\Admin\AddDiscountType;
use App\Form\Admin\AddProductType;
use App\QueryBuilder\AdminSearch;
use App\Repository\BrandRepository;
use App\Repository\DiscountRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function adminShowCustomers(Request $request, EntityManagerInterface $entityManager): Response
    {
        $users      = $entityManager->getRepository(User::class)->findAll();
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

        return $this->render('admin/includes/products/show_products.html.twig', [
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

    # Formulaire d'ajout d'un produit / marque / promotion
    /**
     * @throws InvalidSizeException|InvalidTypeException|UploadException|EntityNotFoundException
     */
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/add/{entity}', name: 'admin_add_item')]
    public function adminAddItem(Request $request, ProductRepository $productRepository, BrandRepository $brandRepository): Response
    {
        $entity = $request->get('entity');
        $errors = [];

        /** @var Form $form */
        switch ($entity) {
            case 'brand':
                $item       = new Brand();
                $form       = $this->createForm(AddBrandType::class, $item);
                break;
            case 'product':
                $item       = new Product();
                $form       = $this->createForm(AddProductType::class, $item);
                break;
            case 'discount':
                $item       = new Discount();
                $options    = ['brands' => []];
                $brands     = $brandRepository->findBy([], ['name' => 'ASC']);

                $options['brands']['Toutes les marques'] = 999999;

                foreach ($brands as $brand)
                    $options['brands'][ucfirst($brand->getName())] = $brand->getId();

                $form = $this->createForm(AddDiscountType::class, $item, $options);
                break;
            default:
                throw new EntityNotFoundException("L'entité spécifiée n'a pas été trouvée.");
        }

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid()) {

                switch ($entity) {
                    case 'brand':
                        $this->em->persist($item);
                        $this->em->flush();

                        $this->addFlash('success', 'La marque a été ajoutée avec succès.');
                        break;
                    case 'product':
                        $images = $form->get('images')->getData();

                        /** @var UploadedFile $file */
                        foreach ($images as $file) {
                            $image  = new Image($file);
                            $folder = $this->getParameter('products_images_directory');

                            $image->upload($folder);

                            $item->addImage($image);
                        }

                        $this->em->persist($item);
                        $this->em->flush();

                        $this->addFlash('success', 'Le produit a été ajouté avec succès.');
                        break;
                    case 'discount':
                        $productId  = $form->get('product')->getData();

                        if ($productId == '999999')
                            $isDiscounted = $productRepository->findAll();
                        elseif (is_numeric($productId))
                            $isDiscounted = $productRepository->findOneBy(['id' => $productId]);

                        $this->em->persist($item);

                        if (!empty($isDiscounted)) {
                            if (is_iterable($isDiscounted)) {
                                /** @var Product $product */
                                foreach ($isDiscounted as $product) {
                                    $product->setDiscount($item);
                                    $this->em->persist($product);
                                }
                            }
                            else {
                                /** @var Product $isDiscounted */
                                $isDiscounted->setDiscount($item);
                                $this->em->persist($isDiscounted);
                            }
                        }

                        $this->em->flush();

                        $this->addFlash('success', 'La promotion a été ajoutée avec succès.');
                        break;
                    default:
                        throw new EntityNotFoundException("L'entité spécifiée n'a pas été trouvée.");
                }
            }
            else {
                foreach ($form->getErrors(true) as $key => $error)
                    $errors[$key] = $error->getMessage();

                $form->clearErrors(true);

                $this->addFlash('failure', json_encode($errors));
            }

            return $this->redirectToRoute('admin_show_products');
        }

        return $this->renderForm('admin/includes/products/_modal_add_item.html.twig', [
            'form'      => $form,
            'errors'    => $errors
        ]);
    }

    # Récupère les produits du catalogue dépendamment de la marque
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/fetch', name: 'admin_fetch_products')]
    public function adminFetchProductsByBrand(Request $request, ProductRepository $productRepository, BrandRepository $brandRepository): Response
    {
        $brandId    = $request->get('brand');
        $return     = [];

        if ($brandId !== '999999') {
            $brand      = $brandRepository->findOneBy(['id' => $brandId]);
            $products   = $productRepository->findBy(['brand' => $brand]);
        }
        else {
            $products = $productRepository->findBy([], ['name' => 'ASC']);
        }

        foreach ($products as $product) {
            $return[ucfirst($product->getName())] = $product->getId();
        }

        return new JsonResponse(json_encode($return));
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