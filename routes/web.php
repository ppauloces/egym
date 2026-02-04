<?php

use Illuminate\Support\Facades\Route;

// SPA - Vue App (todas as rotas que não são /admin)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin|api|sanctum).*$');

Route::get('/', function () {
    return view('app');
});
