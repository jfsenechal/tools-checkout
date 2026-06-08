<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ScannerController;
use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication (issue a bearer token from username + password)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('api.login');

/*
|--------------------------------------------------------------------------
| Public scanner endpoints (used by the browser PWA at /scanner)
|--------------------------------------------------------------------------
*/
Route::prefix('scanner')->group(function () {
    Route::post('/scan', [ScannerController::class, 'scan'])->name('scanner.scan');
    Route::post('/scan-worker', [ScannerController::class, 'scanWorker'])->name('scanner.scan-worker');
    Route::get('/workers', [ScannerController::class, 'workers'])->name('scanner.workers');
    Route::post('/checkout', [ScannerController::class, 'checkout'])->name('scanner.checkout');
    Route::post('/return', [ScannerController::class, 'return'])->name('scanner.return');
    Route::get('/workers/{worker}/tools', [ScannerController::class, 'workerTools'])->name('scanner.worker-tools');
});

/*
|--------------------------------------------------------------------------
| Token-protected API (per-user bearer token via Laravel Sanctum)
|--------------------------------------------------------------------------
| Issue a token with: php artisan api:token {email}
| Send it as: Authorization: Bearer <token>
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()))->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Read resources
    Route::get('/workers', [WorkerController::class, 'index'])->name('api.workers.index');
    Route::get('/tools', [ToolController::class, 'index'])->name('api.tools.index');
    Route::get('/checkouts', [CheckoutController::class, 'index'])->name('api.checkouts.index');

    // Scanner workflow
    Route::post('/scan', [ScannerController::class, 'scan'])->name('api.scan');
    Route::post('/scan-worker', [ScannerController::class, 'scanWorker'])->name('api.scan-worker');
    Route::post('/checkout', [ScannerController::class, 'checkout'])->name('api.checkout');
    Route::post('/return', [ScannerController::class, 'return'])->name('api.return');
});
