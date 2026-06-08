<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\TokensRelationManager;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('can create an API token and reveal it in a copyable modal', function () {
    $user = User::factory()->create();

    $component = livewire(TokensRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewUser::class,
    ])
        ->callAction(TestAction::make('createToken')->table(), [
            'name' => 'Scanner device',
            'abilities' => ['*'],
        ])
        ->assertHasNoActionErrors()
        ->assertActionMounted('revealToken');

    $token = $user->tokens()->where('name', 'Scanner device')->first();

    expect($token)->not->toBeNull();
    expect($component->get('generatedToken'))->toContain((string) $token->getKey());
});

it('requires a name when creating a token', function () {
    $user = User::factory()->create();

    livewire(TokensRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewUser::class,
    ])
        ->callAction(TestAction::make('createToken')->table(), [
            'name' => null,
        ])
        ->assertHasActionErrors(['name' => 'required']);

    expect($user->tokens()->count())->toBe(0);
});

it('can revoke an API token', function () {
    $user = User::factory()->create();
    $user->createToken('To revoke');
    $token = $user->tokens()->first();

    livewire(TokensRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewUser::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$token])
        ->callAction(TestAction::make('revoke')->table($token))
        ->assertHasNoActionErrors();

    expect($user->tokens()->count())->toBe(0);
});
