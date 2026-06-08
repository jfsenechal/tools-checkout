<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('returns a token and user info for valid credentials', function () {
    $user = User::factory()->create([
        'username' => 'jdoe',
        'password' => Hash::make('secret-password'),
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'jdoe',
        'password' => 'secret-password',
        'device_name' => 'pixel-7',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'username', 'first_name', 'last_name', 'name', 'email', 'created_at'],
        ])
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.username', 'jdoe');

    expect($response->json('token'))->toBeString()->not->toBeEmpty();
    expect($user->tokens()->where('name', 'pixel-7')->exists())->toBeTrue();
});

it('defaults the token name when no device name is given', function () {
    $user = User::factory()->create([
        'username' => 'jdoe',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/login', [
        'username' => 'jdoe',
        'password' => 'secret-password',
    ])->assertOk();

    expect($user->tokens()->where('name', 'api')->exists())->toBeTrue();
});

it('rejects an invalid password', function () {
    User::factory()->create([
        'username' => 'jdoe',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/login', [
        'username' => 'jdoe',
        'password' => 'wrong-password',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');

    expect(User::first()->tokens()->count())->toBe(0);
});

it('rejects an unknown username', function () {
    $this->postJson('/api/login', [
        'username' => 'ghost',
        'password' => 'whatever',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('validates required fields', function () {
    $this->postJson('/api/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'password']);
});

it('returns the authenticated user from the token', function () {
    $user = User::factory()->create(['username' => 'jdoe']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.username', 'jdoe');
});

it('throttles repeated failed login attempts', function () {
    User::factory()->create([
        'username' => 'jdoe',
        'password' => Hash::make('secret-password'),
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', [
            'username' => 'jdoe',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    $this->postJson('/api/login', [
        'username' => 'jdoe',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Déconnecté.');

    expect($user->tokens()->count())->toBe(0);
});
