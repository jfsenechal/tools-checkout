<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

final class ListWorkers extends ListRecords
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printQrCodes')
                ->label('Imprimer QR Codes')
                ->icon(Heroicon::QrCode)
                ->color('info')
                ->url(route('print.qrcodes'), shouldOpenInNewTab: true),
            Actions\CreateAction::make()
                ->label('Nouvel agent')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
