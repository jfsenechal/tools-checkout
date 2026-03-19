<?php

declare(strict_types=1);

namespace App\Ldap;

use App\Models\User;
use Exception;
use Illuminate\Support\Str;

final class UserHandler
{
    /**
     * @throws Exception
     */
    public static function createUserFromLdap(array $data): ?User
    {
        $username = $data['username'];
        if (User::where('username', $username)->first()) {
            throw new Exception('Utilisateur déjà existant');
        }
        if ($userLdap = UserLdap::query()->findBy('sAMAccountName', $username)) {
            $dataUser = User::generateDataFromLdap($userLdap);
            $dataUser['username'] = $username;
            $dataUser['password'] = Str::password();

            return User::create($dataUser);
        }
        throw new Exception('Utilisateur introuvable dans la LDAP');
    }
}
