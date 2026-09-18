@extends('pdf.layout')

@section('titulo_reporte', 'Reporte de Compras por Proveedor')

@section('contenido')
    <table>
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>NIT</th>
                <th># Activos</th>
                <th>Total Activos (Q)</th>
                <th># Bienes Varios</th>
                <th>Total Bienes Varios (Q)</th>
                <th>Total General (Q)</th>
            </tr>
        </thead>
        <tbody>
            @php $granTotal = 0; @endphp
            @forelse($proveedores as $proveedor)
                @php
                    $totalActivos = $proveedor->activos_sum_costo_original ?? 0;
                    $totalBienes = $proveedor->bienes_varios_sum_costo ?? 0;
                    $totalProveedor = $totalActivos + $totalBienes;
                    $granTotal += $totalProveedor;
                @endphp
                <tr>
                    <td>{{ $proveedor->nombre }}</td>
                    <td>{{ $proveedor->nit ?? 'N/A' }}</td>
                    <td>{{ $proveedor->activos_count }}</td>
                    <td>Q{{ number_format($totalActivos, 2) }}</td>
                    <td>{{ $proveedor->bienes_varios_count }}</td>
                    <td>Q{{ number_format($totalBienes, 2) }}</td>
                    <td>Q{{ number_format($totalProveedor, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 15px;">No hay proveedores registrados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($proveedores->count())
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align:right; font-weight:bold;">TOTAL GENERAL</td>
                    <td style="font-weight:bold;">Q{{ number_format($granTotal, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
