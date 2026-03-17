<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\WorkerResource;
use App\Filament\Resources\Workers;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkers extends ListRecords
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
