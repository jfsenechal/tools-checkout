<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Actions;

use App\Models\Tool;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;

final class DownloadQrLabelAction
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            self::sizeAction('25x25', '25 × 25 mm'),
            self::sizeAction('32x57', '32 × 57 mm'),
        ])
            ->label('Étiquette QR')
            ->icon(Heroicon::Printer)
            ->button()
            ->visible(fn (Tool $record): bool => (bool) $record->qr_code);
    }

    private static function sizeAction(string $size, string $label): Action
    {
        return Action::make('qr_label_'.$size)
            ->label($label)
            ->icon(Heroicon::QrCode)
            ->url(fn (Tool $record): string => route('print.qr-label', [
                'tool' => $record,
                'size' => $size,
            ]))
            ->openUrlInNewTab();
    }
}
