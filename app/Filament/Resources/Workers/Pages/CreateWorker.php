<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\WorkerResource;
use App\Filament\Resources\Workers;
use Filament\Resources\Pages\CreateRecord;

class CreateWorker extends CreateRecord
{
    protected static string $resource = WorkerResource::class;
}
