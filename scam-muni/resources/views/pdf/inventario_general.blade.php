@extends('pdf.layout')

@section('contenido')
<style>
    @page { margin: 0.7cm; size: letter landscape; }
    body { font-family: 'Helvetica', sans-serif; font-size: 7.5pt; color: #333; line-height: 1.1; }

    .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; margin-bottom: 10px; padding-bottom: 5px; }
    .muni-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0; text-transform: uppercase; color: #1e3a8a; }
    .filtros { text-align: center; font-size: 8pt; color: #666; margin-bottom: 8px; }

    .cat-header { font-weight: bold; font-size: 9.5pt; padding: 7px 0 3px 0; border-bottom: 1.5px solid #000; margin-top: 12px; color: #1e3a8a; }

    .main-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .main-table th { background: #1e3a8a; color: white; padding: 4px; font-size: 6.8pt; text-transform: uppercase; border: 1px solid #333; }
    .main-table td { padding: 3px 4px; border: 1px solid #999; font-size: 7.3pt; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row { font-style: italic; font-weight: bold; background: #f3f4f6; }
</style>

<table class="header-table">
    <tr>
        <td width="10%">@if($muni->logo) <img src="{{ public_path('storage/' . $muni->logo) }}" style="width: 45px;"> @endif</td>
        <td width="70%">
            <span class="muni-name">{{ strtoupper($muni->nombre) }}</span><br>
            <span style="font-size: 8pt;">DEPARTAMENTO DE {{ strtoupper($muni->departamento ?? 'QUETZALTENANGO') }}</span>
        </td>
        <td width="20%" align="right">
            <span style="border: 1px solid #333; padding: 4px; font-weight: bold;">ENTIDAD {{ $muni->codigo_muni }}</span>
        </td>
    </tr>
</table>

<div class="report-title">Reporte General de Inventario</div>
@if(!empty($filtrosDescripcion))
    <div class="filtros">Filtrado por: {{ $filtrosDescripcion }}</div>
@endif

@php $granTotal = 0; $granCantidad = 0; @endphp

@foreach($grupos as $categoria => $items)
    @php
        $subTotal = $items->sum('costo_original');
        $granTotal += $subTotal;
        $granCantidad += $items->count();
    @endphp
    <div class="cat-header">{{ strtoupper($categoria) }} ({{ $items->count() }} bienes)</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="8%">Código</th>
                <th width="16%">Descripción</th>
                <th width="8%">Marca</th>
                <th width="7%">Color</th>
                <th width="7%">Estado</th>
                <th width="8%">No. Factura</th>
                <th width="8%">No. Inventario</th>
                <th width="7%">F. Compra</th>
                <th width="7%">Costo (Q)</th>
                <th width="10%">Proveedor</th>
                <th width="8%">Responsable</th>
                <th width="6%">Oficina</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $activo)
                <tr>
                    <td class="text-center"><strong>{{ $activo->codigo_etiqueta }}</strong></td>
                    <td>{{ $activo->descripcion }}</td>
                    <td>{{ $activo->marcaInfo->nombre ?? 'N/A' }}</td>
                    <td>{{ $activo->colorInfo->nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ $activo->estado ?? 'BUENO' }}</td>
                    <td class="text-center">{{ $activo->numero_factura ?? 'S/N' }}</td>
                    <td class="text-center">{{ $activo->numero_inventario ?? 'S/N' }}</td>
                    <td class="text-center">{{ optional($activo->fecha_compra)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($activo->costo_original, 2) }}</td>
                    <td>{{ $activo->proveedor->nombre ?? 'N/A' }}</td>
                    <td>{{ $activo->asignacionActiva?->empleado?->nombre_completo ?? 'Sin asignar' }}</td>
                    <td>{{ $activo->asignacionActiva?->empleado?->puesto_oficial?->oficina?->nombre ?? 'Bodega' }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="8" class="text-right">SUBTOTAL {{ strtoupper($categoria) }}</td>
                <td class="text-right">Q {{ number_format($subTotal, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
@endforeach

<div style="margin-top: 12px; text-align: right; font-weight: bold; font-size: 10pt; color: #1e3a8a;">
    TOTAL GENERAL: {{ $granCantidad }} bienes — Q {{ number_format($granTotal, 2) }}
</div>

<div style="text-align: center; margin-top: 15px; font-size: 7.5pt; color: #888;">
    Generado el {{ now()->format('d/m/Y H:i') }} - Sistema de Inventarios
</div>
@endsection
