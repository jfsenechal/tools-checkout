<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Tables;

use App\Filament\Resources\Workers\Actions\GenerateQrAction;
use App\Filament\Resources\Workers\Actions\GenerateQrCodesBulkAction;
use App\Filament\Resources\Workers\Actions\ViewQrAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

final class WorkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('activeCheckouts.count')
                    ->label('Emprunts actifs')
                    ->counts('activeCheckouts')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Courriel')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('qr_code')
                    ->label('QR')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                GenerateQrAction::make(),
                ViewQrAction::make(),
                EditAction::make()
                    ->icon(Heroicon::PencilSquare),
                DeleteAction::make()
                    ->icon(Heroicon::Trash),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    GenerateQrCodesBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_name', 'asc');
    }
}
