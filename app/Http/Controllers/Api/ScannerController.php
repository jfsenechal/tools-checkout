<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Checkout\CheckoutToolAction;
use App\Actions\Checkout\ReturnToolAction;
use App\DataTransferObjects\CheckoutData;
use App\DataTransferObjects\ReturnData;
use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Models\Worker;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ScannerController extends Controller
{
    /**
     * Scan a QR code and get tool information
     */
    public function scan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code data',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $qrData = json_decode($request->qr_data, true);

            if (! isset($qrData['type']) || $qrData['type'] !== 'tool') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code type',
                ], 400);
            }

            $tool = Tool::with('currentCheckout.worker')
                ->find($qrData['id']);

            if (! $tool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tool not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tool' => [
                        'id' => $tool->id,
                        'name' => $tool->name,

                        'category' => $tool->category,
                        'status' => $tool->status,
                        'is_available' => $tool->is_available,
                        'is_checked_out' => $tool->is_checked_out,
                    ],
                    'current_checkout' => $tool->currentCheckout ? [
                        'id' => $tool->currentCheckout->id,
                        'worker' => [
                            'id' => $tool->currentCheckout->worker->id,
                            'first_name' => $tool->currentCheckout->worker->first_name,
                            'last_name' => $tool->currentCheckout->worker->last_name,
                        ],
                        'checked_out_at' => $tool->currentCheckout->checked_out_at->toIso8601String(),
                        'expected_return_at' => $tool->currentCheckout->expected_return_at?->toIso8601String(),
                        'is_overdue' => $tool->currentCheckout->is_overdue,
                    ] : null,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR code: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of active workers
     */
    public function workers(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $workers = Worker::active()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->limit(50)
            ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'success' => true,
            'data' => $workers,
        ]);
    }

    /**
     * Checkout a tool
     */
    public function checkout(Request $request, CheckoutToolAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tool_id' => 'required|exists:tools,id',
            'worker_id' => 'required|exists:workers,id',
            'expected_return_at' => 'nullable|date',
            'condition_out' => 'nullable|in:excellent,good,fair,poor',
            'checkout_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $checkoutData = CheckoutData::fromRequest($request->all());
            $checkout = $action->execute($checkoutData);

            return response()->json([
                'success' => true,
                'message' => 'Tool checked out successfully',
                'data' => [
                    'checkout_id' => $checkout->id,
                    'tool' => [
                        'name' => $checkout->tool->name,

                    ],
                    'worker' => [
                        'first_name' => $checkout->worker->first_name,
                        'last_name' => $checkout->worker->last_name,
                    ],
                    'checked_out_at' => $checkout->checked_out_at->toIso8601String(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get tools currently checked out by a worker
     */
    public function workerTools(Worker $worker): JsonResponse
    {
        $checkouts = $worker->activeCheckouts()
            ->with('tool')
            ->orderByDesc('checked_out_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'worker' => [
                    'id' => $worker->id,
                    'first_name' => $worker->first_name,
                    'last_name' => $worker->last_name,
                ],
                'checkouts' => $checkouts->map(fn ($checkout) => [
                    'id' => $checkout->id,
                    'tool' => [
                        'id' => $checkout->tool->id,
                        'name' => $checkout->tool->name,
                        'category' => $checkout->tool->category,
                    ],
                    'checked_out_at' => $checkout->checked_out_at->toIso8601String(),
                    'expected_return_at' => $checkout->expected_return_at?->toIso8601String(),
                    'is_overdue' => $checkout->is_overdue,
                ]),
            ],
        ]);
    }

    /**
     * Return a tool
     */
    public function return(Request $request, ReturnToolAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'checkout_id' => 'required|exists:checkouts,id',
            'condition_in' => 'nullable|in:excellent,good,fair,poor',
            'return_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $returnData = ReturnData::fromRequest($request->all());
            $checkout = $action->execute($returnData);

            return response()->json([
                'success' => true,
                'message' => 'Tool returned successfully',
                'data' => [
                    'checkout_id' => $checkout->id,
                    'tool' => [
                        'name' => $checkout->tool->name,

                        'status' => $checkout->tool->status,
                    ],
                    'returned_at' => $checkout->returned_at->toIso8601String(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
