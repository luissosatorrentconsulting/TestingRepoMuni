<?php

use App\Models\Activo;
use App\Models\Municipalidad;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/reporte-historial/{activo}', function (Activo $activo) {
    $muni = Municipalidad::find(config('app.muni_id', 1));
    $activo->load(['historial.entregador', 'historial.receptor', 'asignaciones.empleado']);
    
    $responsableActual = $activo->historial->last()?->receptor?->nombre_completo ?? 'SIN ASIGNAR';

    $pdf = Pdf::loadView('pdf.historial_activo', [
        'activo' => $activo,
        'historial' => $activo->historial,
        'responsable' => $responsableActual,
        'muni' => $muni,
    ]);

    // El método stream() de DomPDF fuerza el visor del navegador
    return $pdf->stream("Historial-{$activo->codigo_etiqueta}.pdf");
})->name('activos.historial.pdf');