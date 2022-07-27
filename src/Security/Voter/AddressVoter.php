<?php

namespace App\Security\Voter;

use App\Entity\Address;
use App\Entity\User;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AddressVoter extends Voter
{
    public const EDIT   = 'CAN_EDIT';
    public const DELETE = 'CAN_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Address;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // An Admin can both Edit and Delete an Address
        if ($this->security->isGranted('ROLE_ADMIN'))
            return true;

        $user = $token->getUser();

        if (!$user instanceof UserInterface)
            return false;

        return self::isOwner($user, $subject);
    }

    # Check if an Address belongs to an User
    protected function isOwner(User $user, Address $address): bool
    {
        return $address->getCustomer() === $user;
    }
}
