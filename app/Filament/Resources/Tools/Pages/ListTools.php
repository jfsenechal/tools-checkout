<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ListTools extends ListRecords
{
    protected static string $resource = ToolResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' outils';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un outil')
                ->icon(Heroicon::Plus),
        ];
    }
}
