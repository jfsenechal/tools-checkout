<?php

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\ToolResource;
use App\Filament\Resources\Tools;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
