<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Discount;
use App\Entity\Image;
use App\Entity\Product;
use App\Exceptions\InvalidSizeException;
use App\Form\Admin\AddAdminType;
use App\Form\Admin\AddAuthorType;
use App\Form\Admin\AddDiscountType;
use App\Form\Admin\AddProductType;
use App\Form\ModifyPasswordType;
use App\QueryBuilder\AdminSearch;
use App\Repository\AddressRepository;
use App\Repository\AuthorRepository;
use App\Repository\DiscountRepository;
use App\Repository\IPRepository;
use App\Repository\OrderRepository;
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
use Spatie\ImageOptimizer\OptimizerChainFactory;
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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/', name: 'admin')]
    public function adminIndex(Request $request): Response
    {
        return $this->render('admin/show.html.twig');
    }

    # Administration des clients
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/customers', name: 'admin_show_customers')]
    public function adminShowCustomers(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customers      = $entityManager->getRepository(User::class)->findAll();
        foreach ($customers as $k => $customer)
            if (in_array('ROLE_ADMIN', $customer->getRoles()))
                unset($customers[$k]);

        return $this->render('admin/includes/customers/show_customers.html.twig', [
            'customers'         => $customers,
        ]);
    }

    # Administration des produits
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/products', name: 'admin_show_products')]
    public function adminShowProducts(Request $request, AuthorRepository $authorRepository, ProductRepository $productRepository, DiscountRepository $discountRepository): Response
    {
        $authors    = $authorRepository->findBy([], ['name' => 'ASC', 'active' => 'DESC']);
        $products   = $productRepository->findBy([], ['author' => 'DESC', 'active' => 'DESC']);
        $discounts  = $discountRepository->findBy([], ['startingDate' => 'ASC']);

        return $this->render('admin/includes/products/show_products.html.twig', [
            'authors'       => $authors,
            'products'      => $products,
            'discounts'     => $discounts
        ]);
    }

    # Recherche de l'Administration des produits
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
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
    # Formulaire d'ajout d'un produit / auteur / promotion
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/products/add/{entity}', name: 'admin_add_item')]
    public function adminAddItem(Request $request, ProductRepository $productRepository, AuthorRepository $authorRepository): Response
    {
        $entity = $request->get('entity');
        $errors = [];

        /** @var Form $form */
        switch ($entity) {
            case 'author':
                $item       = new Author();
                $form       = $this->createForm(AddAuthorType::class, $item);
                break;
            case 'product':
                $item       = new Product();
                $form       = $this->createForm(AddProductType::class, $item);
                break;
            case 'discount':
                $item       = new Discount();
                $options    = ['authors' => []];
                $authors     = $authorRepository->findBy([], ['name' => 'ASC']);

                $options['authors']['Toutes les auteurs'] = 999999;

                foreach ($authors as $author)
                    $options['authors'][ucfirst($author->getName())] = $author->getId();

                $form = $this->createForm(AddDiscountType::class, $item, $options);
                break;
            default:
                throw new EntityNotFoundException("L'entit?? sp??cifi??e n'a pas ??t?? trouv??e.");
        }

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                switch ($entity) {
                    case 'author':
                        $this->em->persist($item);
                        $this->em->flush();

                        $this->addFlash('success', 'L\'auteur a ??t?? ajout?? avec succ??s.');
                        break;
                    case 'product':
                        $images     = $form->get('images')->getData();
                        $optimizer  = OptimizerChainFactory::create();

                        /** @var UploadedFile $file */
                        foreach ($images as $file) {
                            $image  = new Image($file);
                            $folder = $this->getParameter('products_images_directory') . '/' . $item->getAuthor()->getId();

                            $image->upload($folder);

                            $optimizer->optimize($image->getPath());

                            $item->addImage($image);
                        }

                        $this->em->persist($item);
                        $this->em->flush();

                        $this->addFlash('success', 'Le produit a ??t?? ajout?? avec succ??s.');
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

                        $this->addFlash('success', 'La promotion a ??t?? ajout??e avec succ??s.');
                        break;
                    default:
                        throw new EntityNotFoundException("L'entit?? sp??cifi??e n'a pas ??t?? trouv??e.");
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

    # R??cup??re les produits du catalogue d??pendamment de l\'auteur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/products/fetch', name: 'admin_fetch_products')]
    public function adminFetchProductsByAuthor(Request $request, ProductRepository $productRepository, AuthorRepository $authorRepository): Response
    {
        $authorId   = $request->get('author');
        $return     = [];

        if ($authorId !== '999999') {
            $author     = $authorRepository->findOneBy(['id' => $authorId]);
            $products   = $productRepository->findBy(['author' => $author]);
        }
        else {
            $products = $productRepository->findBy([], ['name' => 'ASC']);
        }

        foreach ($products as $product) {
            $return[ucfirst($product->getName())] = $product->getId();
        }

        return new JsonResponse(json_encode($return));
    }

    # R??cup??re les auteurs du catalogue
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/authors/fetch', name: 'admin_fetch_authors', methods: ['GET'])]
    public function adminFetchAuthors(AuthorRepository $authorRepository): JsonResponse
    {
        $authors            = $authorRepository->findAll();
        $arrayCollection    = [];

        foreach ($authors as $author) {
            $arrayCollection[] = [
                'id'    => $author->getId(),
                'name'  => $author->getName()
            ];
        }

        return new JsonResponse(json_encode($arrayCollection));
    }


    /**
     * @throws EntityNotFoundException
     * @throws Exception
     */
    # Edition d'un produit
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/products/edit', name: 'admin_edit_item')]
    public function adminEditItem(Request $request, AuthorRepository $authorRepository, ProductRepository $productRepository, DiscountRepository $discountRepository, OrderRepository $orderRepository, AddressRepository $addressRepository, IPRepository $IPRepository, UserRepository $userRepository): JsonResponse
    {
        $entity     = $request->get('entity');
        $id         = $request->get('id');
        $field      = $request->get('field');
        $value      = $request->get('value');

        /** @var ServiceEntityRepository $repository */
        $repository = ${$entity . 'Repository'};
        if (!$repository)
            throw new EntityNotFoundException('Une erreur est survenue. L\'entit?? ' . $entity . ' n\'a pas ??t?? trouv??e.');

        /** @var Author|Product|Discount $item */
        $item       = $repository->findOneBy(['id' => $id]);
        if (!$item)
            throw new EntityNotFoundException('Une erreur inattendue est survenue. Aucun objet de la classe ' . $entity . ' ne poss??de d\'id ??gal ?? ' . $id);

        if (in_array($field, ['startingDate', 'endingDate']))
            $value = new DateTime($value);

        if ($field === 'author')
            $value = $authorRepository->findOneBy(['id' => $value]);

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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/products/discount/delete/{id}', name: 'admin_delete_discount')]
    public function adminDeleteDiscount(Request $request, Discount $discount): RedirectResponse
    {
        $this->em->remove($discount);
        $this->em->flush();

        return $this->redirectToRoute('admin_show_products');
    }

    # Suppression d'un produit d'une promotion
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
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

                $this->addFlash('success', 'L\'Administrateur a ??t?? ajout?? avec succ??s.');
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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/admins/edit', name: 'admin_edit_admin')]
    public function adminEditAdmin(Request $request, UserRepository $userRepository): Response
    {
        $id         = $request->get('id');
        $field      = $request->get('field');
        $value      = $request->get('value');

        /** @var User $admin */
        $admin      = $userRepository->findOneBy(['id' => $id]);

        if (!$admin)
            throw new NotFoundResourceException('L\'utilisateur demand?? n\'a pas ??t?? trouv??.');

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
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/admins/edit/{id}/password', name: 'admin_edit_admin_password')]
    public function adminEditAdminPassword(Request $request, User $admin, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /* if (($this->isGranted('ROLE_SUPER_ADMIN') || $admin === $this->security->getUser()) { */
        if (false) {
            $errors = [];
            /** @var Form $form */
            $form   = $this->createForm(ModifyPasswordType::class, $admin, [
                'isAdminEdit'   => true,
                'adminRoute'    => $this->generateUrl('admin_edit_admin_password', [
                    'id'    => $admin->getId()
                ])
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted())
            {
                if ($form->isValid()) {
                    $oldPassword = $form->get('old_password')->getData();

                    if ($userPasswordHasher->isPasswordValid($admin, $oldPassword)) {
                        $hashedPassword = $userPasswordHasher->hashPassword(
                            $admin,
                            $form->get('password')->getData()
                        );
                        $admin->setPassword($hashedPassword);

                        $this->em->flush();

                        $this->addFlash('success', 'Le mot de passe a ??t?? modifi?? avec succ??s.');
                    }
                    else
                        $this->addFlash('failure', 'Votre ancien mot de passe ne correspond pas avec ce que vous avez saisi.');

                }
                else {
                    foreach ($form->getErrors(true) as $key => $error)
                        $errors[$key] = $error->getMessage();

                    $form->clearErrors(true);
                }

                return $this->redirectToRoute('admin_show_admins', [], 307);
            }

            return $this->renderForm('admin/includes/admins/_modal_edit_password.html.twig', [
                'form'      => $form,
                'errors'    => $errors
            ]);
        }
        else
            return $this->redirectToRoute('forgot_password_request');
    }

    # Suppression d'un administrateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/admins/delete/{id}', name: 'admin_delete_admin')]
    public function adminDeleteAdmin(Request $request, User $admin): RedirectResponse
    {
        $this->em->remove($admin);
        $this->em->flush();

        return $this->redirectToRoute('admin_show_admins');
    }

    # Affichage des commandes d'un utilisateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/customers/{id}/orders', name: 'admin_show_customer_orders')]
    public function adminShowCustomerOrders(Request $request, User $customer): RedirectResponse
    {
        dd('ici');
        return $this->redirectToRoute('admin_show_admins');
    }

    # Affichage des adresses postales d'un utilisateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/customers/{id}/addresses', name: 'admin_show_customer_addresses')]
    public function adminShowCustomerAddresses(Request $request, User $customer): RedirectResponse
    {
        dd('ici');
        return $this->redirectToRoute('admin_show_admins');
    }

    # Affichage des adresses IP d'un utilisateur
    #[IsGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas acc??der ?? cette page', 403)]
    #[Route(path: '/admin/customers/{id}/ips', name: 'admin_show_customer_ips')]
    public function adminShowCustomerIPs(Request $request, User $customer): RedirectResponse
    {
        dd('ici');
        return $this->redirectToRoute('admin_show_admins');
    }
}