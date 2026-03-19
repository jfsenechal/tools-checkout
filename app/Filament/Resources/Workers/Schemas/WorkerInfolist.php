<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class WorkerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du travailleur')
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('Prénom'),

                        TextEntry::make('last_name')
                            ->label('Nom'),

                        TextEntry::make('email')
                            ->label('Courriel'),

                        TextEntry::make('phone')
                            ->label('Téléphone'),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'gray',
                                'suspended' => 'danger',
                            }),
                    ])->columns(2),

                Section::make('Notes supplémentaires')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])
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
