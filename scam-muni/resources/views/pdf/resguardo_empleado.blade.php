@extends('pdf.layout')

@section('contenido')
<style>
    @page { margin: 0.8cm 1.2cm; }
    body { font-family: 'Helvetica', sans-serif; font-size: 8.5pt; color: #333; line-height: 1.2; }
    
    /* Encabezado */
    .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; margin-bottom: 10px; padding-bottom: 5px; }
    .muni-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    .entidad-badge { background: #f0f4f8; padding: 3px 8px; border-radius: 4px; font-weight: bold; border: 1px solid #d1d5db; font-size: 8pt; }

    .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 15px 0; text-transform: uppercase; color: #1e3a8a; }

    /* Info Empleado */
    .info-box { width: 100%; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; margin-bottom: 15px; }
    .info-table { width: 100%; border-collapse: collapse; }
    .label { font-size: 7pt; color: #6b7280; text-transform: uppercase; font-weight: bold; }
    .value { font-size: 9pt; font-weight: bold; color: #111; }

    /* Tabla de Activos */
    .main-table { width: 100%; border-collapse: collapse; border: 1px solid #333; }
    .main-table th { background: #1e3a8a; color: white; padding: 5px; font-size: 7.5pt; text-transform: uppercase; border: 1px solid #333; }
    .main-table td { padding: 5px; border: 1px solid #ccc; font-size: 8pt; vertical-align: middle; }
    .text-right { text-align: right; }
    .bg-gray { background: #f8fafc; }

    /* Firmas */
    .signature-container { width: 100%; margin-top: 40px; border-collapse: collapse; }
    .signature-box { border: 1px solid #333; width: 33.33%; padding: 0; vertical-align: top; }
    .signature-label { background: #f3f4f6; font-size: 7pt; font-weight: bold; padding: 4px; border-bottom: 1px solid #333; text-align: center; display: block; }
    .signature-content { padding: 10px; text-align: center; min-height: 60px; }
    .signature-name { font-weight: bold; font-size: 8pt; margin-top: 30px; border-top: 1px solid #000; display: inline-block; padding-top: 2px; width: 80%; }
</style>

<table class="header-table">
    <tr>
        <td width="10%">
            @if($muni->logo)
                <img src="{{ public_path('storage/' . $muni->logo) }}" style="width: 50px;">
            @endif
        </td>
        <td width="70%">
            <span class="muni-name">{{ strtoupper($muni->nombre) }}</span><br>
            <span style="font-size: 8pt; color: #666;">DEPARTAMENTO DE {{ strtoupper($muni->departamento ?? 'QUETZALTENANGO') }}</span>
        </td>
        <td width="20%" style="text-align: right;">
            <span class="entidad-badge">ENTIDAD {{ $muni->codigo_muni }}</span>
        </td>
    </tr>
</table>

<div class="report-title">Tarjeta de Responsabilidad de Activos en Resguardo</div>

<div class="info-box">
    <table class="info-table">
        <tr>
            <td width="35%">
                <span class="label">Responsable</span><br>
                <span class="value">{{ strtoupper($empleado->nombre_completo) }}</span>
            </td>
            <td width="35%">
                <span class="label">Puesto</span><br>
                <span class="value">{{ strtoupper($empleado->puesto_oficial->nombre ?? 'N/A') }}</span>
            </td>
            <td width="30%">
                <span class="label">Departamento</span><br>
                <span class="value">{{ strtoupper($empleado->puesto_oficial->departamento->nombre ?? 'N/A') }}</span>
            </td>
        </tr>
    </table>
</div>

<table class="main-table">
    <thead>
        <tr>
            <th width="30">No.</th>
            <th width="90">Código</th>
            <th>Descripción del Bien</th>
            <th width="80">Marca</th>
            <th width="80">Serie</th>
            <th width="60">Estado</th>
            <th width="80">Valor (Q)</th>
        </tr>
    </thead>
    <tbody>
        @php $totalMonto = 0; @endphp
        @forelse($activos as $index => $activo)
            @php $totalMonto += $activo->costo_original; @endphp
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td align="center"><strong>{{ $activo->codigo_etiqueta }}</strong></td>
                <td>{{ strtoupper($activo->descripcion) }}</td>
                <td align="center">{{ strtoupper($activo->marca ?? 'N/A') }}</td>
                <td align="center">{{ strtoupper($activo->serie ?? 'S/S') }}</td>
                <td align="center">{{ strtoupper($activo->estado ?? 'B') }}</td>
                <td class="text-right">{{ number_format($activo->costo_original, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" align="center" style="padding: 20px; color: #666;">No existen activos bajo resguardo para este empleado.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="bg-gray">
            <td colspan="6" class="text-right"><strong>VALOR TOTAL EN RESGUARDO:</strong></td>
            <td class="text-right"><strong>Q {{ number_format($totalMonto, 2) }}</strong></td>
        </tr>
    </tfoot>
</table>

<table class="signature-container">
    <tr>
        <td class="signature-box">
            <span class="signature-label">RESPONSABLE DEL ACTIVO FIJO</span>
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
            <span class="signature-label">DIRECTOR FINANCIERO (DAFIM)</span>
            <div class="signature-content">
                <div class="signature-name">{{ strtoupper($muni->getAutoridadActual('DIRECTOR_FINANCIERO')) }}</div>
            </div>
        </td>
    </tr>
</table>
@endsection