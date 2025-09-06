<?php


declare(strict_types=1);
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => ['ok' => true, 'laravel' => app()->version()]);

Route::prefix('tickets')->group(function () {
    Route::post('/', [TicketController::class, 'store']);          // POST /tickets
    Route::get('/', [TicketController::class, 'index']);          // GET /tickets?q=&status=&category=&page=
    Route::get('export', [TicketController::class, 'export']);     // GET /tickets/export
    Route::get('{ticket}', [TicketController::class, 'show']);           // GET /tickets/{id}
    Route::patch('{ticket}', [TicketController::class, 'update']);         // PATCH /tickets/{id}
    Route::post('{ticket}/classify', [TicketController::class, 'classify'])
        ->middleware('throttle:classify');; // POST /tickets/{id}/classify
});

// stats
Route::get('/stats', [StatsController::class, 'index'])
    ->middleware('throttle:stats');
