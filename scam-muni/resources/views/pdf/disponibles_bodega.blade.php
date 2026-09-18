@extends('pdf.layout')

@section('titulo_reporte', 'Activos Disponibles en Bodega')

@section('contenido')
    <p style="font-size: 9pt; color: #666; margin-bottom: 10px;">
        Bienes que no están de baja y no tienen una asignación vigente a la fecha de este reporte.
    </p>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($activos as $activo)
                @php $total += $activo->costo_original; @endphp
                <tr>
                    <td>{{ $activo->codigo_etiqueta }}</td>
                    <td>{{ $activo->descripcion }}</td>
                    <td>{{ $activo->categoria->nombre ?? 'N/A' }}</td>
                    <td>{{ $activo->estado ?? 'BUENO' }}</td>
                    <td>Q{{ number_format($activo->costo_original, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 15px;">No hay activos disponibles en bodega en este momento.</td>
                </tr>
            @endforelse
        </tbody>
        @if($activos->count())
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">TOTAL ({{ $activos->count() }} bienes)</td>
                    <td style="font-weight:bold;">Q{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
