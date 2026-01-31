<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/scanner', function () {
    return view('scanner');
})->name('scanner');
