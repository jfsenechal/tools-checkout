<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Checkout
 */
final class CheckoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tool' => new ToolResource($this->whenLoaded('tool')),
            'worker' => new WorkerResource($this->whenLoaded('worker')),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'expected_return_at' => $this->expected_return_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'condition_out' => $this->condition_out,
            'condition_in' => $this->condition_in,
            'is_active' => $this->is_active,
            'is_returned' => $this->is_returned,
            'is_overdue' => $this->is_overdue,
            'duration' => $this->duration,
        ];
    }
}
