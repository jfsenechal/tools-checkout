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
