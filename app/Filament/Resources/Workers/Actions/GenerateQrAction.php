<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Actions;

use App\Models\Worker;
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
            ->action(function (Worker $record, QRCodeService $qrService) {
                $qrService->regenerateForWorker($record);

                Notification::make()
                    ->title('Code QR généré')
                    ->success()
                    ->send();
            })
            ->visible(fn (Worker $record) => ! $record->qr_code);
    }
}
