@extends('pdf.layout')

@section('contenido')
<style>
    @page { margin: 0.8cm 1.2cm; }
    body { font-family: 'Helvetica', sans-serif; font-size: 9pt; color: #333; line-height: 1.2; }

    .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; margin-bottom: 10px; padding-bottom: 5px; }
    .muni-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0; text-transform: uppercase; color: #1e3a8a; }
    .rango { text-align: center; font-size: 9pt; color: #666; margin-bottom: 10px; }

    .main-table { width: 100%; border-collapse: collapse; margin-top: 5px; border: 1px solid #333; }
    .main-table th { background: #1e3a8a; color: white; padding: 5px; font-size: 8pt; text-transform: uppercase; border: 1px solid #333; }
    .main-table td { padding: 5px; border: 1px solid #ccc; font-size: 8.5pt; vertical-align: top; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row { font-style: italic; font-weight: bold; background: #f3f4f6; }
    .motivo { font-size: 8pt; color: #555; }
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

<div class="report-title">Reporte de Bajas de Activos</div>
<div class="rango">
    @if($desde && $hasta)
        Del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
    @elseif($desde)
        Desde el {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
    @elseif($hasta)
        Hasta el {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
    @else
        Histórico completo
    @endif
</div>

<table class="main-table">
    <thead>
        <tr>
            <th width="10%">Fecha Baja</th>
            <th width="10%">No. Acta</th>
            <th width="10%">Código</th>
            <th width="20%">Descripción</th>
            <th width="12%">Categoría</th>
            <th width="10%">Costo (Q)</th>
            <th width="28%">Motivo</th>
        </tr>
    </thead>
    <tbody>
        @php $total = 0; @endphp
        @forelse($activos as $activo)
            @php $total += $activo->costo_original; @endphp
            <tr>
                <td class="text-center">{{ optional($activo->fecha_baja)->format('d/m/Y') ?? 'N/D' }}</td>
                <td class="text-center">{{ $activo->acta_baja ?? 'S/N' }}</td>
                <td class="text-center"><strong>{{ $activo->codigo_etiqueta }}</strong></td>
                <td>{{ strtoupper($activo->descripcion) }}</td>
                <td>{{ $activo->categoria->nombre ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($activo->costo_original, 2) }}</td>
                <td class="motivo">{{ $activo->motivo_baja ?? 'Sin motivo registrado' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" align="center" style="padding: 20px; color: #666;">No hay bajas registradas en el período seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if($activos->count())
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL ({{ $activos->count() }} bienes dados de baja)</td>
                <td class="text-right" colspan="2">Q {{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>

<div style="text-align: center; margin-top: 20px; font-size: 8pt; color: #888;">
    Generado el {{ now()->format('d/m/Y H:i') }} - Sistema de Inventarios
</div>
@endsection
