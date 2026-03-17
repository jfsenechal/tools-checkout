<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ScannerController;
use Illuminate\Support\Facades\Route;

Route::prefix('scanner')->group(function () {
    Route::post('/scan', [ScannerController::class, 'scan'])->name('scanner.scan');
    Route::post('/scan-worker', [ScannerController::class, 'scanWorker'])->name('scanner.scan-worker');
    Route::get('/workers', [ScannerController::class, 'workers'])->name('scanner.workers');
    Route::post('/checkout', [ScannerController::class, 'checkout'])->name('scanner.checkout');
    Route::post('/return', [ScannerController::class, 'return'])->name('scanner.return');
    Route::get('/workers/{worker}/tools', [ScannerController::class, 'workerTools'])->name('scanner.worker-tools');
});
