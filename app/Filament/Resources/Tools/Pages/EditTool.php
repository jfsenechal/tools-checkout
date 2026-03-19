<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    public function getTitle(): string
    {
        return $this->record->name .' '.$this->record->manufacturer;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
