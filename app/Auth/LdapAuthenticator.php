<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\User;

interface LdapAuthenticator
{
    /**
     * Verify the given credentials and return the matching user, or null on failure.
     */
    public function checkPassword(string $username, string $password): ?User;
}
