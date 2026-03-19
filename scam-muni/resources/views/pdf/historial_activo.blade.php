@extends('pdf.layout')

@section('titulo_reporte', 'Historial de Movimientos (Hoja de Vida)')

@section('contenido')
    <div style="margin-bottom: 20px;">
        <strong>Activo:</strong> {{ $activo->descripcion }}<br>
        <strong>Código:</strong> {{ $activo->codigo_etiqueta }}<br>
        <strong>Marca/Modelo:</strong> {{ $activo->marca }} / {{ $activo->modelo }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Responsable</th>
                <th>Documento</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activo->asignaciones as $asig)
            <tr>
                <td>{{ date('d/m/Y', strtotime($asig->fecha_asignacion)) }}</td>
                <td>{{ $asig->empleado->nombre_completo }}</td>
                <td>{{ $asig->documento_respaldo ?? 'N/A' }}</td>
                <td>{{ $asig->observaciones }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection