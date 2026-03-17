<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Pages;

use App\Filament\Resources\Checkout\CheckoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCheckout extends EditRecord
{
    protected static string $resource = CheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
