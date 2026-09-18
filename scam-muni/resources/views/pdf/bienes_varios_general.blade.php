@extends('pdf.layout')

@section('titulo_reporte', 'Listado General de Bienes Varios')

@section('contenido')
    <table>
        <thead>
            <tr>
                <th>No. Inventario</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($bienes as $bien)
                @php $total += $bien->costo; @endphp
                <tr>
                    <td>{{ $bien->numero_inventario ?? 'S/N' }}</td>
                    <td>{{ $bien->descripcion }}</td>
                    <td>{{ $bien->categoria->nombre ?? 'N/A' }}</td>
                    <td>{{ $bien->marcaInfo->nombre ?? 'N/A' }}</td>
                    <td>{{ $bien->oficina->nombre ?? 'N/A' }}</td>
                    <td>{{ $bien->estado }}</td>
                    <td>Q{{ number_format($bien->costo, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 15px;">No hay bienes varios registrados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($bienes->count())
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align:right; font-weight:bold;">TOTAL ({{ $bienes->count() }} bienes)</td>
                    <td style="font-weight:bold;">Q{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
