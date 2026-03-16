<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

readonly class ReturnData
{
    public function __construct(
        public int $checkoutId,
        public ?Carbon $returnedAt = null,
        public ?int $returnedBy = null,
        public ?string $conditionIn = 'good',
        public ?string $returnNotes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            checkoutId: $data['checkout_id'],
            returnedAt: isset($data['returned_at']) 
                ? Carbon::parse($data['returned_at']) 
                : null,
            returnedBy: $data['returned_by'] ?? null,
            conditionIn: $data['condition_in'] ?? 'good',
            returnNotes: $data['return_notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'checkout_id' => $this->checkoutId,
            'returned_at' => $this->returnedAt?->toDateTimeString(),
            'returned_by' => $this->returnedBy,
            'condition_in' => $this->conditionIn,
            'return_notes' => $this->returnNotes,
        ];
    }
}
