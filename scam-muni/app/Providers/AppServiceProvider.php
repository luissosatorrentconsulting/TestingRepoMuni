<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number; // IMPORTANTE
use Illuminate\Support\Facades\App; // IMPORTANTE

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
        // Registro de Observers
        \App\Models\Asignacion::observe(\App\Observers\AsignacionObserver::class);

        // Forzar HTTPS en producción (Railway)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // --- SOLUCIÓN AL ERROR INTL ---
        // Forzamos el idioma a español. Esto a veces hace que Laravel 
        // use un formateador interno más sencillo si no encuentra la extensión.
        App::setLocale('es');
        
        try {
            Number::useLocale('es');
        } catch (\Throwable $e) {
            // Si incluso esto falla, al menos evitamos que la app se detenga
        }
    }
}