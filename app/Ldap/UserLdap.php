<?php

declare(strict_types=1);

namespace App\Ldap;

use LdapRecord\Models\Model;

final class UserLdap extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    // public   $filter1 = "(&(|(sAMAccountName=$uid))(objectClass=person))";
    // public   $filter = '(&(objectClass=person)(!(uid=acmarche)))';
}
