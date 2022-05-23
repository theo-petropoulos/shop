<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\UserLoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @throws Exception
     */
    # Inscription d'un utilisateur
    #[Route(path: '/user/register', name: 'user_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user   = new User();
        $errors = [];

        /** @var Form $form */
        $form   = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $hashedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $data->getPassword()
                );
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                try {
                    $extraParams = ['id' => $user->getId()];
                    $this->emailVerifier->sendEmailConfirmation(
                        'register_verify_email',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address('okko.network@gmail.com', 'Stripe Shop'))
                            ->to($user->getEmail())
                            ->subject('Confirmez votre e-mail')
                            ->htmlTemplate('email/registration/confirmation_register.html.twig'),
                        $extraParams
                    );
                } catch (TransportExceptionInterface $e) {
                    throw new Exception($e->getMessage());
                }

                $this->addFlash('success', 'Votre inscription a été prise en compte, un mail de confirmation vient de vous être envoyé.');

                return $this->redirectToRoute('user_login');
            }
            else {
                foreach ($form->getErrors(true) as $key => $error)
                    $errors[$key] = $error->getMessage();

                $form->clearErrors(true);
            }
        }

        return $this->renderForm('user/register.html.twig', [
            'form'      => $form,
            'errors'    => $errors
        ]);
    }

    # Vérifie l'email de l'utilisateur après inscription
    #[Route(path: '/verify/register/email', name: 'register_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id)
            return $this->redirectToRoute('user_register');

        $user = $userRepository->find($id);

        if (null === $user)
            return $this->redirectToRoute('user_register');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('user_register');
        }

        return $this->redirectToRoute('user_login');
    }
}
