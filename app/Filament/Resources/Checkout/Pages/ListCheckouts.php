<?php

namespace App\Filament\Resources\Checkout\Pages;

use App\Filament\Resources\Checkout;
use App\Filament\Resources\CheckoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckouts extends ListRecords
{
    protected static string $resource = CheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
