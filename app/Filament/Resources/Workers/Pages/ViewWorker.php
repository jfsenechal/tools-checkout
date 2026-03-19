<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewWorker extends ViewRecord
{
    protected static string $resource = WorkerResource::class;

    public function getTitle(): string
    {
        return $this->record->first_name.' '.$this->record->last_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
