<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }



    public function boot(): void
{
    \App\Models\Asignacion::observe(\App\Observers\AsignacionObserver::class);
    if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        
}
}
