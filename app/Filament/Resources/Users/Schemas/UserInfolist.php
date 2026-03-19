<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'utilisateur')
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('Prénom'),

                        TextEntry::make('last_name')
                            ->label('Nom'),

                        TextEntry::make('email')
                            ->label('Email'),
                    ])->columns(2),

                Section::make('Dates')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime(),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }
}
