<?php

namespace App\Security;

use App\Entity\IP;
use App\Entity\User;
use App\Repository\IPRepository;
use App\Repository\UserRepository;

/*
 * Enables multiple adress IPs verifications, such as :
 *
 * - Blacklisted IP
 * - IP that failed login multiple times
 * - IP that registered multiple accounts
 * - User login with a new IP
 * - Store IP address for a new User
 *
 */
class IPVerifier
{
    public function __construct(IPRepository $IPRepository, UserRepository $userRepository)
    {
    }

    public function belongsToUser(IP $ip, User $user): bool
    {
        return true;
    }

    public function verifyIPAdressUponLogin(): bool
    {
        return false;
    }

    public function isAuthorizedIp(IP $ip): bool
    {
        return true;
    }
}
