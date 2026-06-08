<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Worker
 */
final class WorkerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'active_checkouts_count' => $this->whenCounted('activeCheckouts'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
