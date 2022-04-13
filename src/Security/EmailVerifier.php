<?php

namespace App\Security;

use App\Entity\IP;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->verifyEmailHelper    = $helper;
        $this->mailer               = $mailer;
        $this->entityManager        = $manager;
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
                break;
            case 'ip':
                /** @var IP $extraParam */
                $extraParam->setUser($user);
                $this->entityManager->persist($extraParam);
                break;
            default:break;
        }

        $this->entityManager->flush();
    }
}
