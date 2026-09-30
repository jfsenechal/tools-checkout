<?php

declare(strict_types=1);

use App\Auth\LdapAuthService;
use App\Models\User;
use LdapRecord\Testing\DirectoryFake;
use LdapRecord\Testing\LdapFake;

beforeEach(function () {
    $this->userDn = 'cn=jdoe,dc=local,dc=com';

    $this->ldap = DirectoryFake::setup('default')->getLdapConnection();
    $this->ldap->expect([
        LdapFake::operation('search')->andReturn([
            ['dn' => [$this->userDn], 'samaccountname' => ['jdoe']],
        ]),
    ]);
});

afterEach(function () {
    DirectoryFake::tearDown();
});

it('returns null without crashing when the bind fails with no diagnostic message', function () {
    User::factory()->create(['username' => 'jdoe']);

    $this->ldap->shouldReturnDiagnosticMessage(null);

    expect(app(LdapAuthService::class)->checkPassword('jdoe', 'wrong-password'))->toBeNull();
});

it('returns the user when the bind succeeds', function () {
    $user = User::factory()->create(['username' => 'jdoe']);

    $this->ldap->shouldAllowBindWith($this->userDn);

    expect(app(LdapAuthService::class)->checkPassword('jdoe', 'secret-password'))
        ->id->toBe($user->id);
});
