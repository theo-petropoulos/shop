<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Entity\User;
use App\Form\AddAddressType;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PaymentController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $em, private MailerInterface $mailer) {}

    # Sélection de l'adresse pour un invité avant le paiement
    #[Route('/gcheckout/address', name: 'guest_checkout_set_address')]
    public function guestCheckoutSetAddress(Request $request): Response
    {
        if ($this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('user_checkout_set_address');

        $address    = new Address();
        $form       = $this->createForm(AddAddressType::class, $address, [
            'isVisitor' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($address);
            $this->em->flush();

            return $this->redirectToRoute('guest_checkout', [
                'id' => $address->getId()
            ], 307);
        }

        return $this->renderForm('visitor/address.html.twig', [
            'form'  => $form
        ]);
    }

    # Sélection de l'adresse pour un utilisateur authentifié avant le paiement
    #[Route('/ucheckout/address', name: 'user_checkout_set_address')]
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    public function userCheckoutSetAddress(Request $request, EntityManagerInterface $entityManager, AddressRepository $addressRepository, OrderRepository $orderRepository): Response
    {
        if (!$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('guest_checkout_set_address');

        /** @var User $user */
        $user       = $this->security->getUser();
        $addresses  = $addressRepository->findBy(['customer' => $user], ['id' => 'ASC']);
        foreach($addresses as $address) {
            if ($orderRepository->findBy(['address' => $address]))
                $address->setDeletable(false);
        }
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

    /**
     * @throws ApiErrorException
     */
    # Paiement Stripe pour un utilisateur non authentifié
    #[Route('/gcheckout/payment/{id}', name: 'guest_checkout')]
    public function guestCheckout(Request $request, Address $address, ProductRepository $productRepository, $stripeSecret): Response
    {
        if ($this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('user_checkout_set_address');

        $email          = $request->get('add_address')['email'] ?? null;

        if (empty($email))
            throw new NotFoundResourceException('Aucun e-mail n\'a été saisi pour le paiement.');

        Stripe::setApiKey($stripeSecret);

        $arrayCart      = json_decode($request->cookies->get('cart'), true);

        $products       = [];
        $cart           = new Cart($productRepository);
        $cart->getCartFromCookie((array) $arrayCart);

        $customer       = Customer::create([
            'id'        => bin2hex(openssl_random_pseudo_bytes(18)),
            'name'      => $address->getLastName() . ' ' . $address->getFirstName(),
            'email'     => $email
        ]);

        return $this->createStripeOrder(null, $cart, $products, $customer, $address);
    }

    /**
     * @throws ApiErrorException
     */
    # Paiement Stripe pour un utilisateur authentifié
    #[Route('/ucheckout/payment', name: 'user_checkout')]
    public function userCheckout(Request $request, ProductRepository $productRepository, AddressRepository $addressRepository, $stripeSecret): Response
    {
        if (!$this->isGranted('ROLE_USER'))
            return $this->redirectToRoute('guest_checkout_set_address');

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
                'email'     => $user->getEmail()
            ]);
        }
        else
            $customer       = $customerExists->data[0];

        return $this->createStripeOrder($user, $cart, $products, $customer, $address);
    }

    /**
     * @throws ApiErrorException
     * @throws TransportExceptionInterface
     */
    # Paiement avec succès
    #[Route('/ucheckout/success', name: 'user_checkout_success')]
    public function paymentSuccess(Request $request, OrderRepository $orderRepository, UserRepository $userRepository, $stripeSecret): RedirectResponse
    {
        Stripe::setApiKey($stripeSecret);

        $session    = Session::retrieve($request->get('session_id'));
        $orderId    = $request->get('order_id');

        $order      = $orderRepository->find($orderId);

        if (empty($order))
            throw new InvalidRequestException('Une erreur est survenue : La commande n\'a pas été trouvée.');
        else {
            if ($user = $userRepository->findOneBy(['id' => $order->getCustomer()?->getId()]))
                $userMail = $user->getEmail();
            else
                $userMail = Customer::retrieve($session->customer)->email;
        }

        if ($userMail) {
            $address    = $order->getAddress();
            $details    = $order->getOrderDetails();
            $detArray   = [];

            /** @var OrderDetail $detail */
            foreach ($details as $detail) {
                $detArray[] = [
                    'product'   => $detail->getProduct(),
                    'quantity'  => $detail->getProductQuantity(),
                    'total'     => $detail->getTotal()
                ];
            }

            $email      = new TemplatedEmail();
            $email
                ->from(new \Symfony\Component\Mime\Address('okko.network@gmail.com', 'MinimalShop'))
                ->to($userMail)
                ->subject('Confirmation de votre commande #' . $orderId)
                ->htmlTemplate('email/order/confirmation_order.html.twig')
                ->context([
                    'lastName'  => $user?->getLastName() ?? $address->getLastName(),
                    'firstName' => $user?->getFirstName() ?? $address->getFirstName(),
                    'address'   => [
                        'shipTo'    => $address->getLastName() . ' ' . $address->getFirstName(),
                        'city'      => $address->getCity(),
                        'number'    => $address->getStreetNumber(),
                        'street'    => $address->getStreetName(),
                        'addition'  => $address->getStreetAddition()
                    ],
                    'order'     => [
                        'id'        => $orderId,
                        'date'      => $order->getPurchaseDate()->format('d/m/Y'),
                        'details'   => $detArray
                    ]
                ]);
            $this->mailer->send($email);

            setcookie('cart', '', -1, '/');

            $order->setStatus(Order::STATUS_PAID);
            $this->em->flush();
        }
        else
            throw new InvalidRequestException('Une erreur est survenue : Le client n\'a pas été trouvé.');

        $this->addFlash('success', "Merci pour votre achat !\r\nUn e-mail de confirmation vient de vous être envoyé.");

        if ($user)
            return $this->redirectToRoute('user_show_orders');
        else
            return $this->redirectToRoute('home');
    }

    # Paiement sans succès
    #[Route('/ucheckout/failure', name: 'user_checkout_failure')]
    public function paymentFailure(Request $request, OrderRepository $orderRepository, EntityManagerInterface $em): RedirectResponse
    {
        $orderId    = $request->get('order_id');

        $order      = $orderRepository->find($orderId);

        $order->setStatus(Order::STATUS_CANCELLED);
        $em->flush();

        $this->addFlash('warning', 'Le paiement n\'a pas abouti');

        return $this->redirectToRoute('show_cart', $request->query->all());
    }

    /**
     * Génère la commande et les détails en base de données, ainsi que la commande Stripe
     *
     * @param User|null $user
     * @param Cart $cart
     * @param array $products
     * @param Customer $customer
     * @param Address $address
     *
     * @return RedirectResponse
     *
     * @throws ApiErrorException
     */
    public function createStripeOrder(?User $user, Cart $cart, array $products, Customer $customer, Address $address): RedirectResponse
    {
        $order          = new Order();
        $order
            ->setAddress($address)
            ->setPurchaseDate(new \DateTime("today"))
            ->setStatus(Order::STATUS_PENDING);

        if ($user)
            $order->setCustomer($user);

        $this->em->persist($order);
        $this->em->flush();

        foreach ($cart->getCart() as $array) {
            /** @var Product $product */
            $product = $array['product'];
            /** @var int $quantity */
            $quantity = $array['quantity'];

            $products[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice() * 100,
                ],
                'quantity' => $quantity
            ];

            $orderDetail = new OrderDetail();
            $orderDetail
                ->setOrder($order)
                ->setProduct($product)
                ->setProductQuantity($quantity)
                ->setTotal($product->getPrice() * $quantity);

            $this->em->persist($orderDetail);
        }
        $this->em->flush();

        $session = Session::create([
            'customer' => $customer,
            'line_items' => [$products],
            'mode' => 'payment',
            'payment_intent_data' => [
                'shipping' => [
                    'name' => $address->getLastName() . ' ' . $address->getFirstName(),
                    'address' => [
                        'city' => $address->getCity(),
                        'country' => 'France',
                        'line1' => $address->getStreetNumber() . ' ' . $address->getStreetName(),
                        'line2' => $address->getStreetAddition(),
                        'postal_code' => $address->getPostalCode()
                    ]
                ]
            ],
            'success_url' => $this->generateUrl('user_checkout_success', ['order_id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('user_checkout_failure', ['order_id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        return $this->redirect($session->url, 303);
    }
}
