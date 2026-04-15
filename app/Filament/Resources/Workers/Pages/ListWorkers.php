<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ListWorkers extends ListRecords
{
    protected static string $resource = WorkerResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' agents';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvel agent')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
