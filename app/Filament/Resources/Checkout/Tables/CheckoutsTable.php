<?php

declare(strict_types=1);

namespace App\Filament\Resources\CheckoutResource\Tables;

use App\Filament\Resources\CheckoutResource\Actions\ReturnToolAction;
use App\Models\Checkout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CheckoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Outil')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Checkout $record): string => $record->tool->category ?? ''),

                Tables\Columns\TextColumn::make('worker.last_name')
                    ->label('Travailleur')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Checkout $record): string => $record->worker->first_name),

                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Date d\'emprunt')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_return_at')
                    ->label('Retour prévu')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Retourné')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Non retourné')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('is_overdue')
                    ->label('En retard')
                    ->boolean()
                    ->trueIcon(Heroicon::ExclamationTriangle)
                    ->trueColor('danger')
                    ->falseIcon(Heroicon::CheckCircle)
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('condition_out')
                    ->label('État (sortie)')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('condition_in')
                    ->label('État (retour)')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Emprunts actifs')
                    ->query(fn (Builder $query): Builder => $query->whereNull('returned_at'))
                    ->default(),

                Tables\Filters\Filter::make('overdue')
                    ->label('En retard')
                    ->query(fn (Builder $query): Builder => $query->whereNull('returned_at')
                        ->where('expected_return_at', '<', now())
                    ),

                Tables\Filters\SelectFilter::make('tool')
                    ->label('Outil')
                    ->relationship('tool', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('worker')
                    ->label('Travailleur')
                    ->relationship('worker', 'last_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make()
                    ->icon(Heroicon::PencilSquare),
                ReturnToolAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('checked_out_at', 'desc');
    }
}
