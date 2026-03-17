<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Actions;

use App\Services\QRCodeService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class GenerateQrCodesBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('generate_qr_codes')
            ->label('Générer les codes QR')
            ->icon(Heroicon::QrCode)
            ->action(function ($records, QRCodeService $qrService) {
                $qrService->generateBatchWorkers($records->pluck('id')->toArray());

                Notification::make()
                    ->title('Codes QR générés')
                    ->success()
                    ->send();
            });
    }
}
