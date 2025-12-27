<?php

namespace App\Providers;

use App\Models\TripStopPointArrival;
use App\Observers\TripStopPointArrivalObserver;
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
        TripStopPointArrival::observe(TripStopPointArrivalObserver::class);
    }
}
