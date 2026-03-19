<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CheckoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Emprunt')
                    ->schema([
                        TextEntry::make('tool.name')
                            ->label('Outil'),

                        TextEntry::make('worker.last_name')
                            ->label('Travailleur')
                            ->formatStateUsing(fn ($record): string => "{$record->worker->first_name} {$record->worker->last_name}"),

                        TextEntry::make('checked_out_at')
                            ->label('Date d\'emprunt')
                            ->dateTime(),

                        TextEntry::make('expected_return_at')
                            ->label('Retour prévu')
                            ->dateTime()
                            ->placeholder('Non défini'),

                        TextEntry::make('condition_out')
                            ->label('État (sortie)')
                            ->badge()
                            ->placeholder('Non renseigné'),

                        TextEntry::make('checkout_notes')
                            ->label('Notes d\'emprunt')
                            ->columnSpanFull()
                            ->placeholder('Aucune note'),
                    ])->columns(2),

                Section::make('Retour')
                    ->schema([
                        TextEntry::make('returned_at')
                            ->label('Date de retour')
                            ->dateTime()
                            ->placeholder('Non retourné'),

                        IconEntry::make('is_overdue')
                            ->label('En retard')
                            ->boolean(),

                        TextEntry::make('condition_in')
                            ->label('État (retour)')
                            ->badge()
                            ->placeholder('Non renseigné'),

                        TextEntry::make('return_notes')
                            ->label('Notes de retour')
                            ->columnSpanFull()
                            ->placeholder('Aucune note'),
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
