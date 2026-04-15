<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Tables;

use App\Filament\Resources\Workers\Actions\GenerateQrAction;
use App\Filament\Resources\Workers\Actions\GenerateQrCodesBulkAction;
use App\Filament\Resources\Workers\Actions\ViewQrAction;
use App\Models\Worker;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

final class WorkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tag.name')
                    ->label('Tag')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activeCheckouts.count')
                    ->label('Emprunts actifs')
                    ->counts('activeCheckouts')
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('tag_id')
                    ->label('Tag')
                    ->relationship('tag', 'name')
                    ->preload()
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
                    BulkAction::make('printQrCodes')
                        ->label('Imprimer QR')
                        ->icon('heroicon-o-printer')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, $livewire): void {
                            $ids = $records->filter(fn (Worker $worker) => $worker->qr_code !== null)->pluck('id')->join(',');

                            if ($ids !== '') {
                                $livewire->js("window.open('/print/qrcodes?workers={$ids}', '_blank')");
                            }
                        }),
                    GenerateQrCodesBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_name', 'asc');
    }
}
