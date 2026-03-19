<?php

declare(strict_types=1);

namespace App\Repository;

use App\Ldap\UserLdap;

final class UserRepository
{
    public static function listUsersFromLdap(): array
    {
        $users = [];
        foreach (UserLdap::all() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! self::isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            $users[$username] = $userLdap;
        }

        usort($users, function (UserLdap $a, UserLdap $b) {
            return strcasecmp($a->getFirstAttribute('sn'), $b->getFirstAttribute('sn'));
        });

        return $users;
    }

    public static function listUsersFromLdapForSelect(): array
    {
        $users = [];
        foreach (self::listUsersFromLdap() as $userLdap) {
            $users[$userLdap->getFirstAttribute('samaccountname')] = $userLdap->getFirstAttribute(
                'sn'
            ).' '.$userLdap->getFirstAttribute('givenname');
        }

        return $users;
    }

    private static function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
