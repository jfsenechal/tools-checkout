<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

readonly class CheckoutData
{
    public function __construct(
        public int $toolId,
        public int $workerId,
        public ?Carbon $checkedOutAt = null,
        public ?Carbon $expectedReturnAt = null,
        public ?int $checkedOutBy = null,
        public ?string $conditionOut = 'good',
        public ?string $checkoutNotes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            toolId: $data['tool_id'],
            workerId: $data['worker_id'],
            checkedOutAt: isset($data['checked_out_at']) 
                ? Carbon::parse($data['checked_out_at']) 
                : null,
            expectedReturnAt: isset($data['expected_return_at']) 
                ? Carbon::parse($data['expected_return_at']) 
                : null,
            checkedOutBy: $data['checked_out_by'] ?? null,
            conditionOut: $data['condition_out'] ?? 'good',
            checkoutNotes: $data['checkout_notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tool_id' => $this->toolId,
            'worker_id' => $this->workerId,
            'checked_out_at' => $this->checkedOutAt?->toDateTimeString(),
            'expected_return_at' => $this->expectedReturnAt?->toDateTimeString(),
            'checked_out_by' => $this->checkedOutBy,
            'condition_out' => $this->conditionOut,
            'checkout_notes' => $this->checkoutNotes,
        ];
    }
}
