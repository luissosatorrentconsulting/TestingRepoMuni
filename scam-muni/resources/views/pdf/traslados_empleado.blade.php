@extends('pdf.layout')

@section('contenido')
<style>
    /* Configuración de Hoja Horizontal */
    @page { 
        margin: 0.8cm; 
        size: letter landscape; 
    }
    
    body { 
        font-family: 'Helvetica', sans-serif; 
        font-size: 8pt; 
        color: #333; 
        line-height: 1.1;
    }
    
    .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; margin-bottom: 10px; padding-bottom: 5px; }
    .muni-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    
    .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0; text-transform: uppercase; color: #1e3a8a; }

    /* Info Box Horizontal */
    .info-box { width: 100%; border: 1px solid #333; margin-bottom: 10px; }
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 4px; border: 1px solid #ccc; }
    .label { font-size: 7pt; color: #666; text-transform: uppercase; font-weight: bold; }
    .value { font-size: 8.5pt; font-weight: bold; }

    /* Tabla Principal aprovechando el ancho horizontal */
    .main-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
    .main-table th { background: #1e3a8a; color: white; padding: 5px; font-size: 7.5pt; text-transform: uppercase; border: 1px solid #333; }
    .main-table td { padding: 5px; border: 1px solid #999; font-size: 8pt; word-wrap: break-word; }
    
    .category-header { font-weight: bold; font-size: 9pt; padding: 8px 0 3px 0; border-bottom: 1.5px solid #000; margin-top: 10px; }
    .total-row { font-style: italic; font-weight: bold; background: #f3f4f6; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    /* Firmas en la parte inferior */
    .signature-container { width: 100%; margin-top: 30px; border-collapse: collapse; }
    .signature-box { border: 1px solid #333; width: 33.33%; padding: 0; vertical-align: top; }
    .signature-label { background: #f3f4f6; font-size: 7pt; font-weight: bold; padding: 4px; border-bottom: 1px solid #333; text-align: center; display: block; }
    .signature-content { padding: 10px; text-align: center; min-height: 50px; }
    .signature-name { font-weight: bold; font-size: 8pt; margin-top: 25px; border-top: 1px solid #000; display: inline-block; padding-top: 2px; width: 85%; }
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

<div class="report-title">Reporte de Traslados de Activos (Descargos)</div>

<div class="info-box">
    <table class="info-table">
        <tr>
            <td width="50%"><span class="label">Entrega:</span> <span class="value">{{ strtoupper($empleado->nombre_completo) }}</span></td>
            <td width="50%"><span class="label">Puesto:</span> <span class="value">{{ strtoupper($empleado->puesto_oficial->nombre ?? 'N/A') }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Departamento:</span> <span class="value">{{ strtoupper($empleado->puesto_oficial->departamento->nombre ?? 'N/A') }}</span></td>
            <td><span class="label">Responsable Depto:</span> <span class="value">________________________________________________</span></td>
        </tr>
    </table>
</div>

@php $granTotal = 0; @endphp

@foreach($traslados->groupBy(fn($t) => $t->activo->categoria->nombre ?? 'SIN CATEGORÍA') as $categoria => $items)
    <div class="category-header">{{ strtoupper($categoria) }}</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="10%">Código</th>
                <th width="20%">Descripción del Bien</th>
                <th width="10%">Marca</th>
                <th width="10%">Modelo</th>
                <th width="10%">Color</th>
                <th width="10%">Fecha</th>
                <th width="10%">Bajas (Q)</th>
                <th width="10%">Saldo (Q)</th>
                <th width="10%">Recibe</th>
            </tr>
        </thead>
        <tbody>
            @php $subTotal = 0; @endphp
            @foreach($items as $t)
                @php 
                    $valor = $t->activo->costo_original;
                    $subTotal += $valor;
                    $granTotal += $valor;
                @endphp
                <tr>
                    <td class="text-center"><strong>{{ $t->activo->codigo_etiqueta }}</strong></td>
                    <td>{{ strtoupper($t->activo->descripcion) }}</td>
                    <td class="text-center">{{ strtoupper($t->activo->marca ?? 'N/A') }}</td>
                    <td class="text-center">{{ strtoupper($t->activo->modelo ?? 'N/A') }}</td>
                    <td class="text-center">{{ strtoupper($t->activo->color ?? 'N/A') }}</td>
                    <td class="text-center">{{ $t->fecha_movimiento->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($valor, 2) }}</td>
                    <td class="text-right">0.00</td>
                    <td class="text-center">{{ strtoupper($t->receptor->nombre_completo ?? 'N/A') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL {{ strtoupper($categoria) }}</td>
                <td class="text-right">Q {{ number_format($subTotal, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
@endforeach

<div style="margin-top: 15px; text-align: right; font-weight: bold; font-size: 11pt; color: #1e3a8a;">
    TOTAL GENERAL TRASLADADO: Q {{ number_format($granTotal, 2) }}
</div>

<table class="signature-container">
    <tr>
        <td class="signature-box">
            <span class="signature-label">RESPONSABLE DEL ACTIVO FIJO (ENTREGA)</span>
            <div class="signature-content">
                <div class="signature-name">{{ strtoupper($empleado->nombre_completo) }}</div>
            </div>
        </td>
        <td class="signature-box" style="border-left: none;">
            <span class="signature-label">VO.BO. ALCALDE MUNICIPAL</span>
            <div class="signature-content">
                <div class="signature-name">{{ strtoupper($muni->getAutoridadActual('ALCALDE')) }}</div>
            </div>
        </td>
        <td class="signature-box" style="border-left: none;">
            <span class="signature-label">TESORERO / DIRECTOR FINANCIERO</span>
            <div class="signature-content">
                <div class="signature-name">{{ strtoupper($muni->getAutoridadActual('DIRECTOR_FINANCIERO')) }}</div>
            </div>
        </td>
    </tr>
</table>
@endsection