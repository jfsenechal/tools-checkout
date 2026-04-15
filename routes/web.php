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

Route::get('/print/qrcodes', function (Illuminate\Http\Request $request) {
    $toolIds = $request->query('tools');
    $workerIds = $request->query('workers');

    $tools = collect();
    $workers = collect();

    if ($toolIds) {
        $ids = array_map('intval', explode(',', $toolIds));
        $tools = App\Models\Tool::query()
            ->whereIn('id', $ids)
            ->whereNotNull('qr_code')
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    if ($workerIds) {
        $ids = array_map('intval', explode(',', $workerIds));
        $workers = App\Models\Worker::query()
            ->whereIn('id', $ids)
            ->whereNotNull('qr_code')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    if ($tools->isEmpty() && $workers->isEmpty()) {
        $tools = App\Models\Tool::query()
            ->whereNotNull('qr_code')
            ->with('category')
            ->orderBy('name')
            ->get();

        $workers = App\Models\Worker::query()
            ->whereNotNull('qr_code')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    return view('print-qrcodes', compact('tools', 'workers'));
})->name('print.qrcodes');
