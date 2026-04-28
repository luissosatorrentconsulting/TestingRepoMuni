<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// 1. Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// 2. HACK: El simulador debe ir AQUÍ, antes de arrancar la App
if (!extension_loaded('intl')) {
    if (!class_exists('NumberFormatter')) {
        class NumberFormatter {
            const DECIMAL = 1; const CURRENCY = 2; const PERCENT = 3;
            const SCIENTIFIC = 4; const SPELLOUT = 5; const ORDINAL = 6;
            const DURATION = 7; const PATTERN_RULEBASED = 8; const IGNORE = 0;
            const DEFAULT_STYLE = 1;
            public function __construct($locale, $style, $pattern = null) {}
            public function format($value, $type = null) { return $value; }
            public function setAttribute($attr, $value) { return true; }
            public function setSymbol($attr, $value) { return true; }
            public function setTextAttribute($attr, $value) { return true; }
        }
    }
    if (!function_exists('idn_to_ascii')) {
        function idn_to_ascii($domain) { return $domain; }
    }
}

// 3. Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());