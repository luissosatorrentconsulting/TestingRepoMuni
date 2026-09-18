@extends('pdf.layout')

@section('contenido')
<style>
    @page { margin: 0.8cm; size: letter landscape; }
    body { font-family: 'Helvetica', sans-serif; font-size: 8pt; color: #333; line-height: 1.1; }

    .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; margin-bottom: 10px; padding-bottom: 5px; }
    .muni-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0; text-transform: uppercase; color: #1e3a8a; }

    .oficina-header { font-weight: bold; font-size: 10pt; padding: 8px 0 3px 0; border-bottom: 1.5px solid #000; margin-top: 14px; color: #1e3a8a; }
    .oficina-sub { font-size: 7.5pt; color: #666; font-weight: normal; }

    .main-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    .main-table th { background: #1e3a8a; color: white; padding: 5px; font-size: 7.5pt; text-transform: uppercase; border: 1px solid #333; }
    .main-table td { padding: 4px 5px; border: 1px solid #999; font-size: 8pt; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row { font-style: italic; font-weight: bold; background: #f3f4f6; }
</style>

<table class="header-table">
    <tr>
        <td width="10%">@if($muni->logo) <img src="{{ public_path('storage/' . $muni->logo) }}" style="width: 50px;"> @endif</td>
        <td width="70%">
            <span class="muni-name">{{ strtoupper($muni->nombre) }}</span><br>
            <span style="font-size: 9pt;">DEPARTAMENTO DE {{ strtoupper($muni->departamento ?? 'QUETZALTENANGO') }}</span>
        </td>
        <td width="20%" align="right">
            <span style="border: 1px solid #333; padding: 5px; font-weight: bold;">ENTIDAD {{ $muni->codigo_muni }}</span>
        </td>
    </tr>
</table>

<div class="report-title">Inventario de Activos por Oficina</div>

@php $granTotal = 0; $granCantidad = 0; @endphp

@foreach($grupos as $oficina => $items)
    @php
        $departamento = $items->first()->asignacionActiva?->empleado?->puesto_oficial?->oficina?->departamento?->nombre;
        $subTotal = $items->sum('costo_original');
        $granTotal += $subTotal;
        $granCantidad += $items->count();
    @endphp
    <div class="oficina-header">
        {{ strtoupper($oficina) }}
        @if($departamento)
            <span class="oficina-sub">— {{ strtoupper($departamento) }}</span>
        @endif
    </div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Código</th>
                <th width="28%">Descripción</th>
                <th width="15%">Categoría</th>
                <th width="20%">Responsable</th>
                <th width="10%">Estado</th>
                <th width="15%">Costo (Q)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $activo)
                <tr>
                    <td class="text-center"><strong>{{ $activo->codigo_etiqueta }}</strong></td>
                    <td>{{ strtoupper($activo->descripcion) }}</td>
                    <td>{{ $activo->categoria->nombre ?? 'N/A' }}</td>
                    <td>{{ strtoupper($activo->asignacionActiva?->empleado?->nombre_completo ?? 'SIN ASIGNAR') }}</td>
                    <td class="text-center">{{ $activo->estado ?? 'BUENO' }}</td>
                    <td class="text-right">{{ number_format($activo->costo_original, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">SUBTOTAL {{ strtoupper($oficina) }} ({{ $items->count() }} bienes)</td>
                <td class="text-right">Q {{ number_format($subTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endforeach

<div style="margin-top: 15px; text-align: right; font-weight: bold; font-size: 11pt; color: #1e3a8a;">
    TOTAL GENERAL: {{ $granCantidad }} bienes — Q {{ number_format($granTotal, 2) }}
</div>

<div style="text-align: center; margin-top: 20px; font-size: 8pt; color: #888;">
    Generado el {{ now()->format('d/m/Y H:i') }} - Sistema de Inventarios
</div>
@endsection
