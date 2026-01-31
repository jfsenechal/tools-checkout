<?php

use App\Http\Controllers\Api\ScannerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('scanner')->group(function () {
    Route::post('/scan', [ScannerController::class, 'scan'])->name('scanner.scan');
    Route::get('/workers', [ScannerController::class, 'workers'])->name('scanner.workers');
    Route::post('/checkout', [ScannerController::class, 'checkout'])->name('scanner.checkout');
    Route::post('/return', [ScannerController::class, 'return'])->name('scanner.return');
});
