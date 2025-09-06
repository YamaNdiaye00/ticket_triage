<?php


declare(strict_types=1);
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Serve the built SPA (public/spa/index.html) for any non-API path.
 * This lets users load or refresh deep links like /tickets/123 or /dashboard.
 */
Route::get('/{any}', function () {
    $index = public_path('spa/index.html');
    abort_unless(File::exists($index), 404, 'SPA build not found. Run: npm run build');
    return response(File::get($index), 200)->header('Content-Type', 'text/html');
})->where('any', '^(?!api).*$');
