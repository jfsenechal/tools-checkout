<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ToolController extends Controller
{
    /**
     * List tools.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tools = Tool::query()
            ->with('category')
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->boolean('available'), fn ($query) => $query->available())
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return ToolResource::collection($tools);
    }
}
