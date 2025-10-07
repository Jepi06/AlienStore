<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config as MidtransConfig;

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
    MidtransConfig::$serverKey = config('midtrans.server_key');
    MidtransConfig::$isProduction = config('midtrans.is_production');
    MidtransConfig::$isSanitized = true;
    MidtransConfig::$is3ds = true;
    }
}
