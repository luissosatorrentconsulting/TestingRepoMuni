@extends('pdf.layout')

@section('titulo_reporte', 'Listado General de Activos Fijos')

@section('contenido')
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activos as $activo)
                <tr>
                    <td>{{ $activo->codigo_etiqueta }}</td>
                    <td>{{ $activo->descripcion }}</td>
                    <td>{{ $activo->categoria->nombre }}</td>
                    <td>Q{{ number_format($activo->costo_original, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection