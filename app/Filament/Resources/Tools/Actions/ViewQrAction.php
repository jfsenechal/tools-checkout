<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Actions;

use App\Models\Tool;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

final class ViewQrAction
{
    public static function make(): Action
    {
        return Action::make('view_qr')
            ->label('Voir QR')
            ->icon(Heroicon::Eye)
            ->url(fn (Tool $record): string => $record->qr_code_url)
            ->openUrlInNewTab()
            ->visible(fn (Tool $record) => $record->qr_code);
    }
}
