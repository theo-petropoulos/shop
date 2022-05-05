<?php

namespace App\Security;

use App\Entity\IP;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager, SessionInterface $session)
    {
        $this->verifyEmailHelper    = $helper;
        $this->mailer               = $mailer;
        $this->entityManager        = $manager;
        $this->session              = $session;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email, array $extraParams): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            $extraParams
        );

        $context                            = $email->getContext();
        $context['signedUrl']               = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey']     = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData']    = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user, string $action = 'register', mixed $extraParam = null): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        switch ($action) {
            case 'register':
                $user->setIsVerified(true);
                $this->entityManager->persist($user);
                $this->session->getFlashBag()->add('success', 'Vous pouvez à présent vous connecter.');
                break;
            case 'ip':
                /** @var IP $extraParam */
                $extraParam->setUser($user);
                $this->entityManager->persist($extraParam);
                $this->session->getFlashBag()->add('success', 'Vous pouvez à présent vous connecter.');
                break;
            case 'reset_password':
            default:
                break;
        }

        $this->entityManager->flush();
    }
}
