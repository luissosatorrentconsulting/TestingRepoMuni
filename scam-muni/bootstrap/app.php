<?php

use App\Http\Middleware\SetMunicipalidadContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

// --- COMIENZO DEL HACK PARA RAILWAY (Simulador de Intl) ---
if (!extension_loaded('intl')) {
    if (!class_exists('NumberFormatter')) {
        class NumberFormatter {
            const DECIMAL = 1; const CURRENCY = 2; const PERCENT = 3;
            const SCIENTIFIC = 4; const SPELLOUT = 5; const ORDINAL = 6;
            const DURATION = 7; const PATTERN_RULEBASED = 8; const IGNORE = 0;
            const DEFAULT_STYLE = 1;
            public function __construct($locale = 'en', $style = 1, $pattern = null) {}
            public function format($value, $type = null) { return (string)$value; }
            public function setAttribute($attr, $value) { return true; }
            public function setSymbol($attr, $value) { return true; }
            public function setTextAttribute($attr, $value) { return true; }
            public function formatCurrency($value, $currency) { return $currency . " " . $value; }
        }
    }
    if (!function_exists('idn_to_ascii')) {
        function idn_to_ascii($domain) { return $domain; }
    }
}
// --- FIN DEL HACK ---

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Fija la municipalidad activa según el usuario logueado en cada
        // request. Tiene que correr ANTES de que Laravel resuelva los
        // {parametros} de ruta (SubstituteBindings), porque rutas como
        // /reporte-historial/{activo} dependen de config('app.muni_id')
        // para saber a qué municipalidad filtrar.
        $middleware->appendToGroup('web', SetMunicipalidadContext::class);
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: SetMunicipalidadContext::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();