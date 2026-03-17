<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/scanner', function () {
    return view('scanner');
})->name('scanner');

Route::get('/print/qrcodes', function () {
    $tools = App\Models\Tool::query()
        ->whereNotNull('qr_code')
        ->orderBy('name')
        ->get();

    $workers = App\Models\Worker::query()
        ->whereNotNull('qr_code')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    return view('print-qrcodes', compact('tools', 'workers'));
})->name('print.qrcodes');
