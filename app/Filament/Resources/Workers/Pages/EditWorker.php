<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\WorkerResource;
use App\Filament\Resources\Workers;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
