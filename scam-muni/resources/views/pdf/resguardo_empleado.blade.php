@extends('pdf.layout')

@section('titulo_reporte', 'Constancia de Bienes en Resguardo')

@section('contenido')
    <div style="margin-bottom: 20px;">
        <strong>Empleado:</strong> {{ $empleado->nombre_completo }}<br>
        <strong>DPI:</strong> {{ $empleado->dpi }}<br>
        <strong>Cargo:</strong> {{ $empleado->puesto }}
    </div>

    <p>A continuación se detallan los bienes muebles que se encuentran bajo la responsabilidad y custodia del empleado anteriormente mencionado:</p>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Marca/Modelo</th>
                <th>Fecha de Entrega</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $asig)
            <tr>
                <td>{{ $asig->activo->codigo_etiqueta }}</td>
                <td>{{ $asig->activo->descripcion }}</td>
                <td>{{ $asig->activo->marca }} / {{ $asig->activo->modelo }}</td>
                <td>{{ date('d/m/Y', strtotime($asig->fecha_asignacion)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">No cuenta con bienes asignados actualmente.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 80px;">
        <div style="width: 45%; display: inline-block; border-top: 1px solid #000; text-align: center;">Firma Empleado</div>
        <div style="width: 45%; display: inline-block; border-top: 1px solid #000; text-align: center; float: right;">Sello y Firma Inventarios</div>
    </div>
@endsection