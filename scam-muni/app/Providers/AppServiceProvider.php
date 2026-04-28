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

        // SOLUCIÓN AL ERROR INTL: Forzamos el idioma a español 
    // Esto a veces evita que el formateador busque la extensión si el locale es simple
    App::setLocale('es');
    
    // Si sigue fallando, Filament/Laravel 11 permiten definir el locale por defecto
    Number::useLocale('es');

}
}
