<?php
namespace App\Observers;

use App\Models\Asignacion;
use App\Models\MovimientoActivo;
use Carbon\Carbon;

class AsignacionObserver
{
    public function created(Asignacion $asignacion): void
    {
        // Cerramos cualquier otra asignación vigente del mismo activo: solo la
        // más reciente queda "activa", así el activo vuelve a estar disponible
        // para asignar/reasignar y no queda "atrapado" para siempre.
        Asignacion::where('activo_id', $asignacion->activo_id)
            ->where('id', '!=', $asignacion->id)
            ->where('activa', true)
            ->update(['activa' => false]);

        // 1. Buscamos el último movimiento de este activo para saber quién entrega
        $ultimoMovimiento = MovimientoActivo::where('activo_id', $asignacion->activo_id)
            ->orderBy('fecha_movimiento', 'desc')
            ->first();

        // 2. Registramos el nuevo movimiento en el historial
        MovimientoActivo::create([
            'activo_id'         => $asignacion->activo_id,
            'entregado_por_id'  => $ultimoMovimiento ? $ultimoMovimiento->recibido_por_id : null, 
            'recibido_por_id'   => $asignacion->empleado_id,
            'fecha_movimiento'  => Carbon::now(),
            'tipo_movimiento'   => $ultimoMovimiento ? 'traslado' : 'compra',
            'observaciones'     => 'Asignación automática desde el sistema',
        ]);
    }
}