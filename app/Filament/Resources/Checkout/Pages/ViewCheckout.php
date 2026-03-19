<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Pages;

use App\Filament\Resources\Checkout\CheckoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewCheckout extends ViewRecord
{
    protected static string $resource = CheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
