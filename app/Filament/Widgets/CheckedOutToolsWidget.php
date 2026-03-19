<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Checkout\CheckoutResource;
use App\Models\Checkout;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

final class CheckedOutToolsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Outils empruntés';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Checkout::query()
                    ->whereNull('returned_at')
                    ->with(['tool', 'worker'])
                    ->latest('checked_out_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Outil')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Checkout $record) => CheckoutResource::getUrl('view', ['record' => $record->id])),

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
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_overdue')
                    ->label('En retard')
                    ->boolean()
                    ->trueIcon(Heroicon::ExclamationTriangle)
                    ->trueColor('danger')
                    ->falseIcon(Heroicon::CheckCircle)
                    ->falseColor('success'),
            ])
            ->defaultSort('checked_out_at', 'desc')
            ->paginated([5, 10, 25]);
    }
}
