<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Models\Worker;
use App\Models\Checkout;
use App\Actions\Checkout\CheckoutToolAction;
use App\Actions\Checkout\ReturnToolAction;
use App\DataTransferObjects\CheckoutData;
use App\DataTransferObjects\ReturnData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ScannerController extends Controller
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
            
            if (!isset($qrData['type']) || $qrData['type'] !== 'tool') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code type',
                ], 400);
            }

            $tool = Tool::with('currentCheckout.worker')
                ->where('code', $qrData['code'])
                ->first();

            if (!$tool) {
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
                        'code' => $tool->code,
                        'category' => $tool->category,
                        'status' => $tool->status,
                        'is_available' => $tool->is_available,
                        'is_checked_out' => $tool->is_checked_out,
                    ],
                    'current_checkout' => $tool->currentCheckout ? [
                        'id' => $tool->currentCheckout->id,
                        'worker' => [
                            'id' => $tool->currentCheckout->worker->id,
                            'name' => $tool->currentCheckout->worker->name,
                            'badge_number' => $tool->currentCheckout->worker->badge_number,
                        ],
                        'checked_out_at' => $tool->currentCheckout->checked_out_at->toIso8601String(),
                        'expected_return_at' => $tool->currentCheckout->expected_return_at?->toIso8601String(),
                        'is_overdue' => $tool->currentCheckout->is_overdue,
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR code: ' . $e->getMessage(),
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
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('badge_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'badge_number', 'department']);

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
                        'code' => $checkout->tool->code,
                    ],
                    'worker' => [
                        'name' => $checkout->worker->name,
                        'badge_number' => $checkout->worker->badge_number,
                    ],
                    'checked_out_at' => $checkout->checked_out_at->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
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
                        'code' => $checkout->tool->code,
                        'status' => $checkout->tool->status,
                    ],
                    'returned_at' => $checkout->returned_at->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
