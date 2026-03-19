<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Actions;

use App\Models\Checkout;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;

final class ReturnToolAction
{
    public static function make(): Action
    {
        return Action::make('return')
            ->label('Retourner l\'outil')
            ->icon(Heroicon::ArrowUturnLeft)
            ->color('success')
            ->schema([
                DateTimePicker::make('returned_at')
                    ->label('Date de retour')
                    ->default(now())
                    ->required()
                    ->native(false),

                Select::make('condition_in')
                    ->label('État')
                    ->options([
                        'excellent' => 'Excellent',
                        'good' => 'Bon',
                        'fair' => 'Correct',
                        'poor' => 'Mauvais',
                    ])
                    ->default('good')
                    ->required()
                    ->native(false),

                Textarea::make('return_notes')
                    ->label('Notes')
                    ->rows(3),
            ])
            ->action(function (Checkout $record, array $data): void {
                $record->update([
                    'returned_at' => $data['returned_at'],
                    'condition_in' => $data['condition_in'],
                    'return_notes' => $data['return_notes'] ?? null,
                    'returned_by' => auth()->id(),
                ]);

                $tool = $record->tool;
                if (in_array($data['condition_in'], ['poor', 'fair'])) {
                    $tool->markAsInMaintenance();
                } else {
                    $tool->markAsAvailable();
                }
            })
            ->visible(fn (Checkout $record): bool => ! $record->is_returned)
            ->requiresConfirmation();
    }
}
