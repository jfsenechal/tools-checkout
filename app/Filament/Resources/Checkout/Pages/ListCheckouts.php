<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Pages;

use App\Filament\Resources\Checkout\CheckoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCheckouts extends ListRecords
{
    protected static string $resource = CheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
