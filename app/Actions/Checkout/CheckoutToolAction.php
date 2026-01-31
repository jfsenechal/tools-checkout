<?php

namespace App\Actions\Checkout;

use App\Models\Checkout;
use App\Models\Tool;
use App\Models\Worker;
use App\DataTransferObjects\CheckoutData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutToolAction
{
    /**
     * Execute the checkout action
     */
    public function execute(CheckoutData $data): Checkout
    {
        return DB::transaction(function () use ($data) {
            // Verify tool is available
            $tool = Tool::findOrFail($data->toolId);
            
            if (!$tool->is_available) {
                throw new \Exception("Tool '{$tool->name}' is not available for checkout.");
            }

            // Verify worker is active
            $worker = Worker::findOrFail($data->workerId);
            
            if (!$worker->is_active) {
                throw new \Exception("Worker '{$worker->name}' is not active.");
            }

            // Create checkout record
            $checkout = Checkout::create([
                'tool_id' => $data->toolId,
                'worker_id' => $data->workerId,
                'checked_out_at' => $data->checkedOutAt ?? now(),
                'expected_return_at' => $data->expectedReturnAt,
                'checked_out_by' => $data->checkedOutBy,
                'condition_out' => $data->conditionOut ?? 'good',
                'checkout_notes' => $data->checkoutNotes,
            ]);

            // Update tool status
            $tool->markAsCheckedOut();

            return $checkout->fresh(['tool', 'worker']);
        });
    }
}
