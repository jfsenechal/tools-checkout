<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkerResource;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WorkerController extends Controller
{
    /**
     * List workers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $workers = Worker::query()
            ->withCount('activeCheckouts')
            ->when($request->boolean('active'), fn ($query) => $query->active())
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 25));

        return WorkerResource::collection($workers);
    }
}
