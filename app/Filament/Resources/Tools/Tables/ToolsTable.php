<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Tables;

use App\Enums\StatusToolEnum;
use App\Filament\Resources\Tools\Actions\GenerateQrAction;
use App\Filament\Resources\Tools\Actions\GenerateQrCodesBulkAction;
use App\Filament\Resources\Tools\Actions\ViewQrAction;
use App\Models\Tool;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

final class ToolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Tool $record): string => $record->description ?? ''),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('qr_code')
                    ->label('Code QR')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(StatusToolEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'Power Tools' => 'Outils électriques',
                        'Hand Tools' => 'Outils à main',
                        'Measuring Tools' => 'Outils de mesure',
                        'Safety Equipment' => 'Équipement de sécurité',
                        'Ladders & Scaffolding' => 'Échelles & Échafaudages',
                        'Other' => 'Autre',
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
            ->defaultSort('created_at', 'desc');
    }
}
