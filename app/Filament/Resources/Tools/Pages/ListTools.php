<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

final class ListTools extends ListRecords
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon(Heroicon::Plus),
        ];
    }
}
