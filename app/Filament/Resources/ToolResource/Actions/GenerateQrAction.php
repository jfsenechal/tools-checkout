<?php

declare(strict_types=1);

namespace App\Filament\Resources\ToolResource\Actions;

use App\Models\Tool;
use App\Services\QRCodeService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class GenerateQrAction
{
    public static function make(): Action
    {
        return Action::make('generate_qr')
            ->label('Générer QR')
            ->icon(Heroicon::QrCode)
            ->color('info')
            ->action(function (Tool $record, QRCodeService $qrService) {
                $qrService->regenerateForTool($record);

                Notification::make()
                    ->title('Code QR généré')
                    ->success()
                    ->send();
            })
            ->visible(fn (Tool $record) => ! $record->qr_code);
    }
}
