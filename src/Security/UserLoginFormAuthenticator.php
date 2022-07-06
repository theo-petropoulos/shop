<?php

namespace App\Security;

use App\Entity\IP;
use App\Repository\IPRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserLoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'user_login';

    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private SessionInterface $session;
    private EmailVerifier $emailVerifier;
    private AccessDecisionManagerInterface $accessManager;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session, EmailVerifier $emailVerifier, AccessDecisionManagerInterface $accessManager)
    {
        $this->urlGenerator     = $urlGenerator;
        $this->entityManager    = $entityManager;
        $this->tokenStorage     = $tokenStorage;
        $this->session          = $session;
        $this->emailVerifier    = $emailVerifier;
        $this->accessManager    = $accessManager;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge()
            ]
        );
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        /** @var IPRepository $IPRepository */
        $IPRepository   = $this->entityManager->getRepository(IP::class);

        $currentIP  = $IPRepository->findOneBy(['address' => $request->getClientIp()]) ?? (new IP())->setAddress($request->getClientIp());
        $user       = $token->getUser();
        $knownIPs   = $user->getIP();

        // If the User is logging in for the first time, set the current IP as one of his
        if ($knownIPs->isEmpty() && !$currentIP->getUser()) {
            $currentIP->setUser($user);
            $this->entityManager->persist($currentIP);
            $this->entityManager->flush();
        }
        else {
            // If the IP address is linked to the User
            if ($currentIP->belongsToUser($user)) {
                if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
                    return new RedirectResponse($targetPath);
                }
            }
            // If the IP is set for another User or if the IP doesn't match one of the User's
            else {
                $this->entityManager->persist($currentIP);
                $this->entityManager->flush();
                try {
                    $extraParams = ['id' => $user->getId(), 'ip' => $currentIP->getId()];
                    $this->emailVerifier->sendEmailConfirmation(
                        'login_verify_ip',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address('okko.network@gmail.com', 'Stripe Shop'))
                            ->to($user->getEmail())
                            ->subject('Connexion depuis un nouvel appareil')
                            ->htmlTemplate('email/login/confirmation_ip.html.twig'),
                        $extraParams
                    );
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
                $this->tokenStorage->setToken();
                $this->session->invalidate();
                $this->session->getFlashBag()->add('warning', 'Vous venez de vous connecter depuis un nouvel appareil. Un e-mail de confirmation vient de vous être envoyé.');

                return new RedirectResponse($this->urlGenerator->generate('user_login'));
            }
        }

        $roles = $user->getRoles();

        if (array_intersect(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $roles))
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        else
            return new RedirectResponse($this->urlGenerator->generate('user_show_profile'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
