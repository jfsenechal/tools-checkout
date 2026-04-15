<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ToolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'outil')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom')
                            ->columnSpanFull(),

                        TextEntry::make('category.name')
                            ->label('Catégorie')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informations d\'achat')
                    ->schema([
                        TextEntry::make('manufacturer')
                            ->label('Fabricant'),

                        TextEntry::make('model')
                            ->label('Modèle'),
                    ])->columns(2)
                    ->collapsible(),

                Section::make('QR Code')
                    ->schema([
                        IconEntry::make('qr_code')
                            ->label('Code QR généré')
                            ->boolean(),
                    ])
                    ->collapsible(),

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
