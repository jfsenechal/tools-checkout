<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CheckoutController extends Controller
{
    /**
     * List checkouts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $checkouts = Checkout::query()
            ->with(['tool.category', 'worker'])
            ->when($request->boolean('active'), fn ($query) => $query->active())
            ->when($request->boolean('returned'), fn ($query) => $query->returned())
            ->when($request->boolean('overdue'), fn ($query) => $query->overdue())
            ->when($request->integer('worker_id'), fn ($query, int $workerId) => $query->forWorker($workerId))
            ->when($request->integer('tool_id'), fn ($query, int $toolId) => $query->forTool($toolId))
            ->orderByDesc('checked_out_at')
            ->paginate($request->integer('per_page', 25));

        return CheckoutResource::collection($checkouts);
    }
}
