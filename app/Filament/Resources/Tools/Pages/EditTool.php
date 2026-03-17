<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
