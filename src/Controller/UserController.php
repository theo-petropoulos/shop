<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
    public function userEditPassword(Request $request, UserInterface $user): Response
    {
        return $this->render('user/includes/edit_password.html.twig', [
            'user'  => $user
        ]);
    }

    # Modification des adresses
    #[IsGranted('ROLE_USER', null, 'Vous ne pouvez pas accéder à cette page', 403)]
    #[Route(path: '/user/profile/addresses', name: 'user_edit_addresses')]
    public function userEditAddresses(Request $request, UserInterface $user): Response
    {
        return $this->render('user/includes/edit_addresses.html.twig', [
            'user'  => $user
        ]);
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