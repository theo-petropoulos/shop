<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\AddAddressType;
use App\Repository\AddressRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PaymentController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $em) {}

    # Sélection de l'adresse pour un invité avant paiement
    #[Route('/gcheckout/address', name: 'guest_checkout_set_address')]
    public function guestCheckoutSetAddress(Request $request): Response
    {
        if ($this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('user_checkout_set_address');

        // todo : Ajouter une adresse et submit => checkout
    }

    #[Route('/ucheckout/address', name: 'user_checkout_set_address')]
    public function userCheckoutSetAddress(Request $request, EntityManagerInterface $entityManager, AddressRepository $addressRepository): Response
    {
        if (!$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('guest_checkout_set_address');

        /** @var User $user */
        $user       = $this->security->getUser();
        $addresses  = $addressRepository->findBy(['customer' => $user], ['id' => 'ASC']);
        $address    = new Address($user);
        $errors     = [];

        /** @var Form $form */
        $form       = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid()) {
                $entityManager->persist($address);
                $entityManager->flush();

                $this->addFlash('success', 'L\'adresse a été ajoutée avec succès.');

                return $this->redirectToRoute('user_checkout_set_address');
            }
            else {
                foreach ($form->getErrors(true) as $key => $error)
                    $errors[$key] = $error->getMessage();

                $form->clearErrors(true);
            }
        }

        return $this->renderForm('payment/user/set_address.html.twig', [
            'user'      => $user,
            'addresses' => $addresses,
            'form'      => $form,
            'errors'    => $errors
        ]);
    }

    #[Route('/gcheckout/payment', name: 'guest_checkout')]
    public function guestCheckout(): Response
    {
        if ($this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('user_checkout');

        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    # Paiement Stripe pour un utilisateur authentifié
    #[Route('/ucheckout/payment', name: 'user_checkout')]
    public function userCheckout(Request $request, ProductRepository $productRepository, AddressRepository $addressRepository, $stripeSecret): Response
    {
        if (!$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('guest_checkout');

        /** @var Address $address */
        $addressId      = $request->request->get('address');
        $address        = $addressRepository->find($addressId);

        if (empty($address))
            throw new NotFoundResourceException('Aucune adresse n\'a été sélectionnée pour le paiement.');

        Stripe::setApiKey($stripeSecret);

        $arrayCart      = json_decode($request->cookies->get('cart'), true);

        $products       = [];
        $cart           = new Cart($productRepository);
        $cart->getCartFromCookie((array) $arrayCart);

        /** @var User $user */
        $user           = $this->security->getUser();

        $customerExists = Customer::search([
            'query'     => "email:'" . $user->getEmail() . "'"
        ]);

        if (empty($customerExists->data)) {
            $customer       = Customer::create([
                'id'        => $user->getId(),
                'name'      => $user->getLastName() . ' ' . $user->getFirstName(),
                'email'     => $user->getEmail()/*,
                'address'   => [
                    'city'          => $address->getCity(),
                    'country'       => 'France',
                    'line1'         => $address->getStreetNumber() . ' ' . $address->getStreetName(),
                    'line2'         => $address->getStreetAddition(),
                    'postal_code'   => $address->getPostalCode()
                ]*/
            ]);
        }
        else
            $customer       = $customerExists->data[0];

        $order          = new Order();
        $order
            ->setCustomer($user)
            ->setAddress($address)
            ->setPurchaseDate(new \DateTime("today"))
            ->setStatus(Order::STATUS_PENDING);

        $this->em->persist($order);
        $this->em->flush();

        foreach ($cart->getCart() as $array)
        {
            /** @var Product $product */
            $product    = $array['product'];
            /** @var int $quantity */
            $quantity   = $array['quantity'];

            $products[] = [
                'price_data'    => [
                    'currency'      => 'eur',
                    'product_data'  => [
                        'name'          => $product->getName(),
                    ],
                    'unit_amount'   => $product->getPrice() * 100,
                ],
                'quantity'      => $quantity
            ];
        }

        $session = Session::create([
            'customer'          => $customer,
            'line_items'        => [$products],
            'mode'              => 'payment',
            'payment_intent_data'   => [
                'shipping'  => [
                    'name'      => $customer->name ?? 'Inconnu',
                    'address'   => $customer->address->toArray()
                ]
            ],
            'success_url'       => $this->generateUrl('user_checkout_success', ['order_id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'        => $this->generateUrl('user_checkout_failure', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        return $this->redirect($session->url, 303);
    }

    /**
     * @throws ApiErrorException
     */
    # Paiement avec succès
    #[Route('/ucheckout/success', name: 'user_checkout_success')]
    public function paymentSuccess(Request $request, $stripeSecret): Response
    {
        Stripe::setApiKey($stripeSecret);

        $session = Session::retrieve($request->get('session_id'));
        // todo : Supprimer le cookie, actualiser l'order, envoyer un mail
        dd($request, $session);

    }

    # Paiement sans succès
    #[Route('/ucheckout/failure', name: 'user_checkout_failure')]
    public function paymentFailure(Request $request): RedirectResponse
    {
        $this->addFlash('warning', 'Le paiement n\'a pas abouti');
        return $this->redirectToRoute('show_cart', $request->query->all());
    }
}
