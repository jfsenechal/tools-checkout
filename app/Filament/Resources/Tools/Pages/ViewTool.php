<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewTool extends ViewRecord
{
    protected static string $resource = ToolResource::class;

    public function getTitle(): string
    {
        return $this->record->name.' '.$this->record->manufacturer;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
