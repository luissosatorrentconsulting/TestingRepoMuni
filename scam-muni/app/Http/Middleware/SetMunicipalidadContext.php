<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve, en cada request, cuál es "la municipalidad activa" y la deja
 * disponible en config('app.muni_id') — el mismo valor que antes venía fijo
 * desde .env (MUNI_ID) y que usan todos los modelos para filtrar sus datos.
 *
 * - Usuario normal: siempre su propia municipalidad (users.municipalidad_id).
 * - Super Admin (municipalidad_id null): la que haya elegido en "Cambiar
 *   Municipalidad" (guardada en sesión). Si todavía no eligió ninguna, el
 *   valor queda en null a propósito: todo lo que dependa de una
 *   municipalidad concreta muestra vacío en vez de mezclar datos de todas.
 */
class SetMunicipalidadContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $municipalidadId = $user->isSuperAdmin()
                ? session('acting_as_municipalidad_id')
                : $user->municipalidad_id;

            config(['app.muni_id' => $municipalidadId]);
        }

        return $next($request);
    }
}
