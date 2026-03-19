<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('can create a new user', function () {
    $user = User::factory()->make();

    visit('/admin')
        ->click('Utilisateurs')
        ->click('New user')
        ->fill('form.username', $user->username)
        ->fill('form.email', $user->email)
        ->press('.fi-ac-btn-action[type=submit]')
        ->assertSee('Created');

    assertDatabaseHas('users', [
        'username' => $user->username,
        'email' => $user->email,
    ]);
});

it('can edit an existing user', function () {
    $newRecord = User::factory()->make();

    visit('/admin')
        ->click('Utilisateurs')
        ->click('Edit')
        ->fill('form.first_name', $newRecord->first_name)
        ->click('.fi-ac-btn-action[type=submit]')
        ->assertSee('Saved');

    assertDatabaseHas('users', [
        'first_name' => $newRecord->first_name,
    ]);
});

it('can delete an existing user', function () {
    visit('/admin')
        ->click('Utilisateurs')
        ->click('Edit')
        ->click('Delete')
        ->click('.fi-modal-window button[type=submit]')
        ->assertSee('Deleted');

    assertDatabaseMissing('users', [
        'id' => auth()->user()->id,
    ]);
});
