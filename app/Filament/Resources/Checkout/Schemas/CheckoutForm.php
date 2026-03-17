<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class CheckoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'emprunt')
                    ->schema([
                        Select::make('tool_id')
                            ->label('Outil')
                            ->relationship('tool', 'name', fn (Builder $query) => $query->where('status', 'available'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Seuls les outils disponibles sont affichés'),

                        Select::make('worker_id')
                            ->label('Travailleur')
                            ->relationship('worker', 'last_name', fn (Builder $query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Seuls les travailleurs actifs sont affichés'),

                        DateTimePicker::make('checked_out_at')
                            ->label('Date d\'emprunt')
                            ->default(now())
                            ->required()
                            ->native(false),

                        DateTimePicker::make('expected_return_at')
                            ->label('Date de retour prévue')
                            ->native(false)
                            ->after('checked_out_at'),

                        Select::make('condition_out')
                            ->label('État (sortie)')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Bon',
                                'fair' => 'Correct',
                                'poor' => 'Mauvais',
                            ])
                            ->default('good')
                            ->required()
                            ->native(false),

                        Textarea::make('checkout_notes')
                            ->label('Notes d\'emprunt')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informations de retour')
                    ->schema([
                        DateTimePicker::make('returned_at')
                            ->label('Date de retour')
                            ->native(false)
                            ->after('checked_out_at'),

                        Select::make('condition_in')
                            ->label('État (retour)')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Bon',
                                'fair' => 'Correct',
                                'poor' => 'Mauvais',
                            ])
                            ->native(false),

                        Textarea::make('return_notes')
                            ->label('Notes de retour')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
