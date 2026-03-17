<?php

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\ToolResource;
use App\Filament\Resources\Tools;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTools extends ListRecords
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
