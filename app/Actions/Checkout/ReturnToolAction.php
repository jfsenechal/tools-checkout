<?php

namespace App\Actions\Checkout;

use App\Models\Checkout;
use App\DataTransferObjects\ReturnData;
use Illuminate\Support\Facades\DB;

class ReturnToolAction
{
    /**
     * Execute the return action
     */
    public function execute(ReturnData $data): Checkout
    {
        return DB::transaction(function () use ($data) {
            // Find the active checkout
            $checkout = Checkout::with('tool')
                ->findOrFail($data->checkoutId);

            if ($checkout->is_returned) {
                throw new \Exception('This tool has already been returned.');
            }

            // Update checkout record
            $checkout->update([
                'returned_at' => $data->returnedAt ?? now(),
                'returned_by' => $data->returnedBy,
                'condition_in' => $data->conditionIn ?? 'good',
                'return_notes' => $data->returnNotes,
            ]);

            // Update tool status based on condition
            $tool = $checkout->tool;
            
            if (in_array($data->conditionIn, ['poor', 'fair'])) {
                $tool->markAsInMaintenance();
            } else {
                $tool->markAsAvailable();
            }

            return $checkout->fresh(['tool', 'worker']);
        });
    }
}
