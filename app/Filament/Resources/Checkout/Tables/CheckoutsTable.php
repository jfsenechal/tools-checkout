<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Tables;

use App\Filament\Resources\Checkout\Actions\ReturnToolAction;
use App\Filament\Resources\Checkout\CheckoutResource;
use App\Models\Checkout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
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
                    ->url(fn (Checkout $record) => CheckoutResource::getUrl('view', ['record' => $record->id]))
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
            ->filtersFormColumns(2)
            ->filtersFormWidth(Width::TwoExtraLarge)
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

                Tables\Filters\Filter::make('checked_out_at')
                    ->label('Date d\'emprunt')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Du')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Au')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('checked_out_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('checked_out_at', '<=', $date)
                            );
                    })
                    ->columns(2)
                    ->columnSpanFull(),
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
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make()
                    ->icon(Heroicon::PencilSquare),
                ReturnToolAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function ($records) {
                            $records->each(function (Checkout $record) {
                                if (! $record->returned_at) {
                                    $record->tool->markAsAvailable();
                                }
                            });
                        }),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->defaultSort('checked_out_at', 'desc');
    }
}
