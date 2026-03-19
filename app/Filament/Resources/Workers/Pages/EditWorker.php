<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    public function getTitle(): string
    {
        return $this->record->first_name.' '.$this->record->last_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
