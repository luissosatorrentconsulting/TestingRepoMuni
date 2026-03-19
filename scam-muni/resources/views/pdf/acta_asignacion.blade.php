@extends('pdf.layout')

@section('titulo_reporte', 'Acta de Entrega y Responsabilidad')

@section('contenido')
    <p>Se hace entrega del siguiente bien al empleado <strong>{{ $asignacion->empleado->nombre_completo }}</strong>:</p>
    
    <table>
        <tr><th>Código</th><td>{{ $asignacion->activo->codigo_etiqueta }}</td></tr>
        <tr><th>Descripción</th><td>{{ $asignacion->activo->descripcion }}</td></tr>
        <tr><th>Marca/Modelo</th><td>{{ $asignacion->activo->marca }} / {{ $asignacion->activo->modelo }}</td></tr>
    </table>

    <p style="margin-top: 30px;"><strong>Observaciones:</strong> {{ $asignacion->observaciones ?? 'Sin observaciones' }}</p>

    <div style="margin-top: 80px;">
        <div style="width: 45%; display: inline-block; border-top: 1px solid #000; text-align: center;">Entrega (Suministros)</div>
        <div style="width: 45%; display: inline-block; border-top: 1px solid #000; text-align: center; float: right;">Recibe: {{ $asignacion->empleado->nombre_completo }}</div>
    </div>
@endsection