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

Route::get('/print/qr-label/{tool}', function (App\Models\Tool $tool, Illuminate\Http\Request $request) {
    abort_unless((bool) $tool->qr_code, 404);

    $size = $request->query('size') === '32x57' ? '32x57' : '25x25';

    $tool->loadMissing('category');

    return view('print-qr-label', compact('tool', 'size'));
})->name('print.qr-label');

Route::get('/print/qr-dymo/{tool}', function (App\Models\Tool $tool, Illuminate\Http\Request $request, App\Services\DymoLabelGenerator $generator) {
    $size = $request->query('size') === '32x57' ? '32x57' : '25x25';

    $tool->loadMissing('category');

    return response($generator->generateForTool($tool, $size), 200, [
        'Content-Type' => 'application/xml',
        'Content-Disposition' => 'attachment; filename="'.$generator->filename($tool, $size).'"',
    ]);
})->name('print.qr-dymo');
