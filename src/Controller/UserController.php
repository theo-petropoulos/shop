<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddAddressType;
use App\Form\ModifyPasswordType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use JetBrains\PhpStorm\Pure;

class UserController extends AbstractController
{
    #[Pure]
    public function __construct(private ManagerRegistry $doctrine) {}

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
        $form = $this->createForm(ModifyPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->renderForm('user/includes/edit_password.html.twig', [
            'user'  => $user,
            'form'  => $form
        ]);
    }

    # Affichage des adresses
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses', name: 'user_show_addresses')]
    public function userShowAddresses(Request $request, UserInterface $user, AddressRepository $addressRepository, EntityManagerInterface $entityManager): Response
    {
        $addresses  = $addressRepository->findBy(['customer' => $user], ['id' => 'ASC']);
        $address    = new Address($user);
        $form       = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($address);
            $entityManager->flush();
            $this->addFlash('success', 'L\'adresse a été ajoutée avec succès.');

            return $this->redirectToRoute('user_show_addresses');
        }

        return $this->renderForm('user/address/index.html.twig', [
            'user'      => $user,
            'addresses' => $addresses,
            'form'      => $form
        ]);
    }

    # Modification d'une adresse
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses/edit/{addressId}', name: 'user_edit_address')]
    public function userEditAddress(Request $request, UserInterface $user, AddressRepository $addressRepository, EntityManagerInterface $entityManager, int $addressId): Response
    {
        $address    = $addressRepository->findOneBy(['id' => $addressId]);
        $form       = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        return $this->renderForm('user/address/_modal_edit_address.html.twig', [
            'address'   => $address,
            'user'      => $user,
            'form'      => $form
        ]);
    }

    # Suppression d'une adresse
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses/delete/{addressId}', name: 'user_delete_address')]
    public function userDeleteAddress(Request $request, UserInterface $user, AddressRepository $addressRepository, EntityManagerInterface $entityManager, int $addressId): Response
    {
        $address    = $addressRepository->findOneBy(['id' => $addressId]);
        $entityManager->remove($address);
        $entityManager->flush();

        return $this->redirectToRoute('user_show_addresses');
    }

    # Commandes de l'utilisateur
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/orders', name: 'user_show_orders')]
    public function userShowOrders(Request $request, UserInterface $user): Response
    {
        return $this->render('user/includes/show_orders.html.twig', [
            'user'  => $user
        ]);
    }

    # Suppression du compte
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/delete', name: 'user_delete_account')]
    public function userDeleteAccount(Request $request, UserInterface $user): Response
    {
        return $this->render('user/includes/delete.html.twig', [
            'user'  => $user
        ]);
    }
}