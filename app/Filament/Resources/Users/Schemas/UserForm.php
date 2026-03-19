<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema;
        /*     TextInput::make('password')
                 ->password()
                 ->required(fn ($livewire): bool => $livewire instanceof CreateUser)
                 ->revealable(filament()->arePasswordsRevealable())
                 ->rule(Password::default())
                 ->autocomplete('new-password')
                 ->dehydrated(fn ($state): bool => filled($state))
                 ->dehydrateStateUsing(fn ($state): string => Hash::make($state)),
        */
    }

    public static function add(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('username')
                    ->label('Nom')
                    ->options(UserRepository::listUsersFromLdapForSelect())
                    ->searchable(),
            ]);
    }
}
