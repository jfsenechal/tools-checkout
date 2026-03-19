<?php

declare(strict_types=1);

namespace App\Filament\Resources\Checkout\Pages;

use App\Filament\Resources\Checkout\CheckoutResource;
use App\Models\Checkout;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditCheckout extends EditRecord
{
    protected static string $resource = CheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->icon(Heroicon::Trash)
                ->after(function (Checkout $record) {
                    if (! $record->returned_at) {
                        $record->tool->markAsAvailable();
                    }
                }),
        ];
    }
}
