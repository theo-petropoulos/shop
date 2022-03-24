<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route(path: '/user/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
             return $this->redirectToRoute('user_show_profile');
        }

        // get the login error if there is one
        $error          = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername   = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route(path: '/user/logout', name: 'user_logout')]
    public function logout(): void {}

}
