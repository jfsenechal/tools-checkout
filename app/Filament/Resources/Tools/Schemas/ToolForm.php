<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ToolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'outil')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'Power Tools' => 'Outils électriques',
                                'Hand Tools' => 'Outils à main',
                                'Measuring Tools' => 'Outils de mesure',
                                'Safety Equipment' => 'Équipement de sécurité',
                                'Ladders & Scaffolding' => 'Échelles & Échafaudages',
                                'Other' => 'Autre',
                            ])
                            ->searchable()
                            ->native(false),

                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'available' => 'Disponible',
                                'checked_out' => 'Emprunté',
                                'maintenance' => 'En maintenance',
                                'retired' => 'Retiré',
                            ])
                            ->default('available')
                            ->required()
                            ->native(false),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informations d\'achat')
                    ->schema([
                        TextInput::make('manufacturer')
                            ->label('Fabricant')
                            ->maxLength(255),

                        TextInput::make('model')
                            ->label('Modèle')
                            ->maxLength(255),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }
}
