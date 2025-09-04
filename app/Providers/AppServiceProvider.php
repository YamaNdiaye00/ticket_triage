<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // /stats limiter: 30 requests per minute per IP
        RateLimiter::for('stats', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // (Recommended) classify limiter: 10/min per IP
        RateLimiter::for('classify', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // (Optional) bulk classify limiter: 2/min per IP (only if you expose an API)
        RateLimiter::for('classify-bulk', function (Request $request) {
            return Limit::perMinute(2)->by($request->ip());
        });
    }
}
