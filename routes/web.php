<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response()->file(public_path('spa/index.html'));
})->where('any', '^(?!api).*$');    // Don't swallow /api/*
