<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Discount;
use App\Entity\Image;
use App\Entity\Product;
use App\Errors\ErrorFormatter;
use App\Exceptions\InvalidSizeException;
use App\Form\Admin\AddAdminType;
use App\Form\Admin\AddBrandType;
use App\Form\Admin\AddDiscountType;
use App\Form\Admin\AddProductType;
use App\Form\ModifyPasswordType;
use App\QueryBuilder\AdminSearch;
use App\Repository\BrandRepository;
use App\Repository\DiscountRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AdminController extends AbstractController
{
    private EntityManagerInterface $em;
    private Security $security;
    private EmailVerifier $emailVerifier;

    #[Pure]
    public function __construct(EntityManagerInterface $entityManager, Security $security, EmailVerifier $emailVerifier) {
        $this->em               = $entityManager;
        $this->security         = $security;
        $this->emailVerifier    = $emailVerifier;
    }

    # Accueil Admin
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/', name: 'admin')]
    public function adminIndex(Request $request): Response
    {
        return $this->render('admin/show.html.twig');
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

    /**
     * @throws InvalidSizeException|InvalidTypeException|UploadException|EntityNotFoundException
     */
    # Formulaire d'ajout d'un produit / marque / promotion
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

    # Récupère les marques du catalogue
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/brands/fetch', name: 'admin_fetch_brands', methods: ['GET'])]
    public function adminFetchBrands(BrandRepository $brandRepository): JsonResponse
    {
        $brands             = $brandRepository->findAll();
        $arrayCollection    = [];

        foreach ($brands as $brand) {
            $arrayCollection[] = [
                'id'    => $brand->getId(),
                'name'  => $brand->getName()
            ];
        }

        return new JsonResponse(json_encode($arrayCollection));
    }


    /**
     * @throws EntityNotFoundException
     * @throws Exception
     */
    # Edition d'un produit
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/edit', name: 'admin_edit_item')]
    public function adminEditItem(Request $request, BrandRepository $brandRepository, ProductRepository $productRepository, DiscountRepository $discountRepository): JsonResponse
    {
        $entity     = $request->get('entity');
        $id         = $request->get('id');
        $field      = $request->get('field');
        $value      = $request->get('value');

        /** @var ServiceEntityRepository $repository */
        $repository = ${$entity . 'Repository'};
        if (!$repository)
            throw new EntityNotFoundException('Une erreur est survenue. L\'entité ' . $entity . ' n\'a pas été trouvée.');

        /** @var Brand|Product|Discount $item */
        $item       = $repository->findOneBy(['id' => $id]);
        if (!$item)
            throw new EntityNotFoundException('Une erreur inattendue est survenue. Aucun objet de la classe ' . $entity . ' ne possède d\'id égal à ' . $id);

        if (in_array($field, ['startingDate', 'endingDate']))
            $value = new DateTime($value);

        if ($field === 'brand')
            $value = $brandRepository->findOneBy(['id' => $value]);

        $method     = 'set' . ucfirst($field);
        if (method_exists($item, $method))
            $item->$method($value);
        else
            throw new InvalidArgumentException('Une erreur inattendue est survenue.');

        $this->em->persist($item);
        $this->em->flush();

        return new JsonResponse(json_encode(['status' => 'success']));
    }

    # Suppression d'une promotion
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/discount/delete/{id}', name: 'admin_delete_discount')]
    public function adminDeleteDiscount(Request $request, Discount $discount): RedirectResponse
    {
        $this->em->remove($discount);
        $this->em->flush();

        return $this->redirectToRoute('admin_show_products');
    }

    # Suppression d'un produit d'une promotion
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/products/discount/delete/{discountId}/product/{productId}', name: 'admin_delete_discount_product')]
    #[ParamConverter('discount', Discount::class, ['mapping' => ['discountId' => 'id']])]
    #[ParamConverter('product', Product::class, ['mapping' => ['productId' => 'id']])]
    public function adminDeleteProductFromDiscount(Request $request, Discount $discount, Product $product): RedirectResponse
    {
        $discount->removeProduct($product);
        $this->em->flush();

        return $this->redirectToRoute('admin_show_products');
    }

    # Administration des administrateurs
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins', name: 'admin_show_admins')]
    public function adminShowAdmins(Request $request, UserRepository $userRepository): Response
    {
        $admins     = $userRepository->findByRole('admin');
        $currAdmin  = $this->security->getUser();

        return $this->render('admin/includes/admins/show_admins.html.twig', [
            'admins'    => $admins,
            'currAdmin' => $currAdmin
        ]);
    }

    # Ajout d'un administrateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins/add', name: 'admin_add_admin')]
    public function adminAddAdmin(Request $request): Response
    {
        $errors = [];

        $admin  = new User();

        /** @var Form $form */
        $form   = $this->createForm(AddAdminType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                /** @var User $admin */
                $admin = $form->getData();
                $admin->setRoles(['ROLE_ADMIN']);

                $this->em->persist($admin);
                $this->em->flush();

                $this->addFlash('success', 'L\'Administrateur a été ajouté avec succès.');
            }
            else {
                foreach ($form->getErrors(true) as $key => $error)
                    $errors[$key] = $error->getMessage();

                $form->clearErrors(true);

                $this->addFlash('failure', json_encode($errors));
            }

            return $this->redirectToRoute('admin_show_admins');
        }

        return $this->renderForm('admin/includes/admins/_modal_add_admin.html.twig', [
            'form'      => $form,
            'errors'    => $errors
        ]);
    }

    # Edition d'un administrateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins/edit', name: 'admin_edit_admin')]
    public function adminEditAdmin(Request $request, UserRepository $userRepository): Response
    {
        $id         = $request->get('id');
        $field      = $request->get('field');
        $value      = $request->get('value');

        /** @var User $admin */
        $admin      = $userRepository->findOneBy(['id' => $id]);

        if (!$admin)
            throw new NotFoundResourceException('L\'utilisateur demandé n\'a pas été trouvé.');

        $method     = 'set' . ucfirst($field);
        if (method_exists($admin, $method))
            $admin->$method($value);
        else
            throw new InvalidArgumentException('Une erreur inattendue est survenue.');

        $this->em->persist($admin);
        $this->em->flush();

        return new JsonResponse(json_encode(['status' => 'success']));
    }

    /**
     * @throws TransportExceptionInterface
     */
    # Edition du mot de passe d'un administrateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins/edit/{id}/password', name: 'admin_edit_admin_password')]
    public function adminEditAdminPassword(Request $request, User $admin, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN') || $admin === $this->security->getUser()) {
            $sortedErrors   = [];
            /** @var Form $form */
            $form           = $this->createForm(ModifyPasswordType::class, $admin);

            return $this->renderForm('admin/includes/admins/_modal_edit_password.html.twig', [
                'form'          => $form,
                'sortedErrors'  => $sortedErrors
            ]);
        }
        else {
            $extraParams = ['id' => $admin->getId()];
            $this->emailVerifier->sendEmailConfirmation(
                'user_reset_password',
                $admin,
                (new TemplatedEmail())
                    ->from(new Address('okko.network@gmail.com', 'Stripe Shop'))
                    ->to($admin->getEmail())
                    ->subject('Réinitialisation du mot de passe')
                    ->htmlTemplate('email/login/confirmation_ip.html.twig'),
                $extraParams
            );

            $this->addFlash('success', 'Un email de réinitialisation du mot de passe vient d\'être envoyé.');
            return new RedirectResponse($this->generateUrl('admin_show_admins'));
        }
    }

    # Suppression d'un administrateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/admin/admins/delete/{id}', name: 'admin_delete_admin')]
    public function adminDeleteAdmin(Request $request, User $admin): RedirectResponse
    {
        $this->em->remove($admin);
        $this->em->flush();

        return $this->redirectToRoute('admin_show_admins');
    }

}