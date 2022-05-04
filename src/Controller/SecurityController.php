<?php

namespace App\Controller;

use App\Errors\ErrorFormatter;
use App\Form\ResetPasswordType;
use App\Repository\IPRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\UserLoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    protected EmailVerifier $emailVerifier;
    private EntityManagerInterface $entityManager;
    private UserCheckerInterface $userChecker;
    private UserAuthenticatorInterface $userAuthenticator;

    public function __construct(EntityManagerInterface $entityManager, EmailVerifier $emailVerifier, UserCheckerInterface $userChecker, UserAuthenticatorInterface $userAuthenticator) {
        $this->emailVerifier            = $emailVerifier;
        $this->entityManager            = $entityManager;
        $this->userChecker              = $userChecker;
        $this->userAuthenticator        = $userAuthenticator;
    }

    #[Route(path: '/user/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
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

    #[Route(path: '/user/reset-password', name: 'user_reset_password')]
    public function resetUserPassword(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, UserLoginFormAuthenticator $formAuthenticator): ?Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_show_profile');
        }

        $id             = $request->get('id');
        $sortedErrors   = [];

        if (!$id)
            return $this->redirectToRoute('user_login');

        $user   = $userRepository->find($id);

        if (!$user)
            return $this->redirectToRoute('user_login');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user, 'reset_password');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('failure', 'Le lien que vous avez utilisé est invalide. Veuillez réessayer.');
            return $this->redirectToRoute('user_login');
        }

        /** @var Form $form */
        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $hashedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $data->getPassword()
                );
                $user
                    ->setPassword($hashedPassword)
                    ->setIsVerified(true);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');

                $this->userChecker->checkPreAuth($user);
                return $this->userAuthenticator->authenticateUser($user, $formAuthenticator, $request);
            }
            else {
                $errorFormatter = new ErrorFormatter($form);
                $sortedErrors   = $errorFormatter->sortErrors();
                $form->clearErrors(true);
            }
        }

        return $this->renderForm('user/reset_password.html.twig', [
            'form'          => $form,
            'sortedErrors'  => $sortedErrors
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
