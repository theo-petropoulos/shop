<?php

namespace App\Controller;

use App\Repository\IPRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    protected EmailVerifier $emailVerifier;

    public function __construct(protected ManagerRegistry $doctrine, EmailVerifier $emailVerifier) {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route(path: '/user/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
             return $this->redirectToRoute('user_show_profile');
        }

        $error          = $authenticationUtils->getLastAuthenticationError();
        $lastUsername   = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route(path: '/verify/login/ip', name: 'login_verify_ip')]
    public function verifyUserIp(Request $request, UserRepository $userRepository, IPRepository $ipRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_show_profile');
        }

        $id = $request->get('id');
        $ip = $request->get('ip');

        if ($id === null || $ip === null)
            return $this->redirectToRoute('user_login');

        $user   = $userRepository->find($id);
        $ip     = $ipRepository->find($ip);

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user, 'ip', $ip);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('failure', 'Le lien que vous avez utilisé est invalide. Veuillez réessayer.');
            return $this->redirectToRoute('user_login');
        }

        return $this->redirectToRoute('user_login', [
        ]);
    }

    #[Route(path: '/user/logout', name: 'user_logout')]
    public function logout(): void {}
}
