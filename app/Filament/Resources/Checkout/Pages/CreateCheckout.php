<?php

namespace App\Filament\Resources\CheckoutResource\Pages;

use App\Filament\Resources\CheckoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckout extends CreateRecord
{
    protected static string $resource = CheckoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['checked_out_by'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Update tool status to checked_out
        $this->record->tool->markAsCheckedOut();
    }
}
