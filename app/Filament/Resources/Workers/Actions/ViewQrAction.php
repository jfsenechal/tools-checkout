<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Actions;

use App\Models\Worker;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

final class ViewQrAction
{
    public static function make(): Action
    {
        return Action::make('view_qr')
            ->label('Voir QR')
            ->icon(Heroicon::Eye)
            ->url(fn (Worker $record): string => $record->qr_code_url)
            ->openUrlInNewTab()
            ->visible(fn (Worker $record) => $record->qr_code);
    }
}
