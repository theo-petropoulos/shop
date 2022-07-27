<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Errors\ErrorFormatter;
use App\Form\AddAddressType;
use App\Form\ModifyPasswordType;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\ServiceFactory;
use Sasedev\MpdfBundle\Factory\MpdfFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use JetBrains\PhpStorm\Pure;

class UserController extends AbstractController
{
    #[Pure]
    public function __construct(protected ManagerRegistry $doctrine, private Security $security) {}

    # Affiche le profil de l'utilisateur
    #[Route(path: '/user/profile/', name: 'user_show_profile')]
    public function showProfileIndex(Request $request, UserInterface $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user'  => $user
        ]);
    }

    # Modification du mot de passe
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/password', name: 'user_edit_password')]
    public function userEditPassword(Request $request, UserInterface $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var Form $form */
        $form           = $this->createForm(ModifyPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->refresh($user);
                $oldPassword = $form->get('old_password')->getData();

                if ($userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                    $hashedPassword = $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    );
                    $user->setPassword($hashedPassword);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');
                }
                else
                    $this->addFlash('failure', 'Votre ancien mot de passe ne correspond pas avec ce que vous avez saisi.');
            }
            else {
                $errorFormatter = new ErrorFormatter($form);
                $sortedErrors   = $errorFormatter->sortErrors();
                $form->clearErrors(true);

                return new JsonResponse(json_encode($sortedErrors));
            }
        }

        /** @var User $user */
        if ($user->isGranted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']))
            return $this->redirectToRoute('admin_show_admins');
        else
            return $this->renderForm('user/includes/edit_password.html.twig', [
                'user'  => $user,
                'form'  => $form
            ]);
    }

    # Affichage des adresses
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses', name: 'user_show_addresses', methods: ['POST', 'GET'])]
    public function userShowAddresses(Request $request, UserInterface $user, AddressRepository $addressRepository, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $addresses  = $addressRepository->findBy(['customer' => $user], ['id' => 'ASC']);
        foreach($addresses as $address) {
            if ($orderRepository->findBy(['address' => $address]))
                $address->setDeletable(false);
        }
        $address    = new Address($user);

        /** @var Form $form */
        $form       = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        $errors = [];

        if ($form->isSubmitted())
        {
            if ($form->isValid()) {
                $entityManager->persist($address);
                $entityManager->flush();

                $this->addFlash('success', 'L\'adresse a été ajoutée avec succès.');

                return $this->redirectToRoute('user_show_addresses');
            }
            else {
                foreach ($form->getErrors(true) as $key => $error)
                    $errors[$key] = $error->getMessage();

                $form->clearErrors(true);
            }
        }

        return $this->renderForm('user/address/show.html.twig', [
            'user'      => $user,
            'addresses' => $addresses,
            'form'      => $form,
            'errors'    => $errors
        ]);
    }

    # Modification d'une adresse
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[IsGranted('CAN_EDIT', 'address', 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses/edit/{id}', name: 'user_edit_address', methods: ['GET', 'POST'])]
    public function userEditAddress(Request $request, UserInterface $user, Address $address, EntityManagerInterface $entityManager): Response
    {
        $form       = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid()) {
                /** @var Address $address */
                $address = $form->getData();

                $entityManager->persist($address);
                $entityManager->flush();

                $this->addFlash('success', 'L\'adresse a été modifiée avec succès.');
            }
            else
                $this->addFlash('failure', 'Votre saisie comporte un ou plusieurs caractères interdits. Veuillez réessayer.');

            $referer = $request->headers->get('referer');
            if ($referer && is_string($referer))
                return $this->redirect($referer);
            else
                return $this->redirectToRoute('user_show_addresses');
        }

        return $this->renderForm('user/address/_modal_edit_address.html.twig', [
            'address'   => $address,
            'user'      => $user,
            'form'      => $form
        ]);
    }

    # Suppression d'une adresse
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[IsGranted('CAN_DELETE', 'address', 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses/delete/{id}', name: 'user_delete_address')]
    public function userDeleteAddress(Request $request, Address $address, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderExist = $orderRepository->findBy(['address' => $address]);

        if (empty($orderExist)) {
            $entityManager->remove($address);
            $entityManager->flush();

            $referer = $request->headers->get('referer');
            if ($referer && is_string($referer))
                return $this->redirect($referer);
        }
        // Todo : Catch exception if order exists

        return $this->redirectToRoute('user_show_addresses');
    }

    # Commandes de l'utilisateur
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/orders', name: 'user_show_orders')]
    public function userShowOrders(Request $request): Response
    {
        /** @var User $user */
        $user   = $this->security->getUser();
        $orders = $user->getOrders();

        /** @var Order $order */
        foreach ($orders as $key => $order)
            if (!in_array($order->getStatus(), Order::FULLFILLED_ORDER))
                unset($orders[$key]);

        return $this->render('user/includes/show_orders.html.twig', [
            'user'      => $user,
            'orders'    => $orders
        ]);
    }


    /**
     * @throws MpdfException
     */
    # Génération de la facture d'une commande
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/orders/{id}/invoice', name: 'order_show_invoice')]
    public function orderShowInvoice(Request $request, Order $order, MpdfFactory $factory): Response
    {
        $mpdf = new Mpdf([
            'margin_left'   => 20,
            'margin_right'  => 15,
            'margin_top'    => 48,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'default_font'  => 'Courier'
        ]);

        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle("Facture - MinimalShop");
        $mpdf->SetAuthor("MinimalShop");
        $mpdf->SetWatermarkText("Payé");
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'Courier';
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($this->renderView('user/order/invoice.html.twig', [
            'order' => $order
        ]));

        return $factory->createDownloadResponse($mpdf, "FACTURE-" . $order->getId() . "-" . $order->getPurchaseDate()->format("dmY"));
    }

    # Suppression du compte
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/delete', name: 'user_delete_account')]
    public function userDeleteAccount(Request $request, UserInterface $user, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $tokenStorage->setToken();
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a bien été supprimé.');

        return $this->redirectToRoute('home');
    }
}