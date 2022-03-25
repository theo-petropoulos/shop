<?php

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

namespace App\Security;

use App\Entity\IP;

class IPVerifier
{
    public function __construct()
    {

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
