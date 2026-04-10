@extends('pdf.layout')

@section('contenido')
    <style>
        @page { margin: 0.8cm 1.2cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 9pt; line-height: 1.2; }
        
        /* Encabezado Compacto y Único */
        .header-mini { width: 100%; border-bottom: 1px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 10px; }
        .muni-info h1 { font-size: 12pt; margin: 0; color: #1e3a8a; font-weight: bold; }
        .muni-info p { font-size: 8pt; margin: 0; color: #666; text-transform: uppercase; }
        .entidad-tag { background: #f0f4f8; padding: 4px 8px; border-radius: 4px; font-weight: bold; border: 1px solid #d1d5db; }

        .report-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0; text-transform: uppercase; text-decoration: underline; }

        /* Filas de Identificación Compactas */
        .id-row { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .id-label { background: #1e3a8a; color: white; padding: 4px 8px; font-weight: bold; width: 100px; font-size: 8pt; text-align: center; }
        .id-code { background: #e0e7ff; border: 1px solid #1e3a8a; padding: 4px 8px; font-weight: bold; width: 120px; text-align: center; }
        .id-value { border: 1px solid #ccc; border-left: none; padding: 4px 10px; font-weight: bold; background: #fff; }

        /* Grid de Detalles Super Compacto */
        .specs-table { width: 100%; border-collapse: collapse; margin-top: 5px; border: 1px solid #ccc; }
        .specs-table td { border: 1px solid #ccc; padding: 3px 6px; width: 33.33%; }
        .s-label { font-size: 7pt; color: #666; font-weight: bold; text-transform: uppercase; display: block; }
        .s-value { font-size: 9pt; font-weight: bold; display: block; }

        /* Tabla de Movimientos Compacta con Cierre */
        .mov-table { width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid #333; }
        .mov-table th { background: #f2f2f2; border-bottom: 1px solid #333; border-right: 1px solid #333; padding: 5px; text-align: left; font-size: 8pt; }
        .mov-table td { padding: 5px; border-bottom: 1px solid #ccc; border-right: 1px solid #333; vertical-align: top; }
        .mov-table tr:last-child td { border-bottom: 1px solid #333; } /* Cierra la última línea */
        
        .date-text { font-weight: bold; color: #1e3a8a; }
        .sub-text { font-size: 7.5pt; color: #777; font-style: italic; }

        .footer { text-align: center; margin-top: 30px; font-size: 8pt; color: #888; }
    </style>

    {{-- Encabezado --}}
    <table class="header-mini">
        <tr>
            <td width="10%">
                @if($muni->logo)
                    <img src="{{ public_path('storage/' . $muni->logo) }}" style="width: 50px;">
                @endif
            </td>
            <td class="muni-info" width="70%">
                <h1>{{ strtoupper($muni->nombre) }}</h1>
                <p>DEPARTAMENTO DE {{ strtoupper($muni->departamento ?? 'XELA') }}</p>
            </td>
            <td width="20%" style="text-align: right;">
                <span class="entidad-tag">ENTIDAD {{ $muni->codigo_muni }}</span>
            </td>
        </tr>
    </table>

    <div class="report-title">Historial y Hoja de Vida del Activo</div>

    {{-- Info Principal Compacta --}}
    <table class="id-row">
        <tr>
            <td class="id-label">ACTIVO</td>
            <td class="id-code">{{ $activo->codigo_etiqueta }}</td>
            <td class="id-value">{{ strtoupper($activo->descripcion) }}</td>
        </tr>
    </table>

    <table class="id-row">
        <tr>
            <td class="id-label">RESPONSABLE</td>
            <td class="id-value" style="border-left: 1px solid #ccc;">{{ strtoupper($responsable) }}</td>
        </tr>
    </table>

    {{-- Especificaciones Técnicas --}}
    <table class="specs-table">
        <tr>
            <td><span class="s-label">Color</span><span class="s-value">{{ $activo->color ?? 'N/A' }}</span></td>
            <td><span class="s-label">Categoría</span><span class="s-value">{{ $activo->categoria->nombre }}</span></td>
            <td><span class="s-label">No. Serie</span><span class="s-value">{{ $activo->serie ?? 'S/S' }}</span></td>
        </tr>
        <tr>
            <td><span class="s-label">Marca</span><span class="s-value">{{ $activo->marca ?? '---' }}</span></td>
            <td><span class="s-label">Modelo</span><span class="s-value">{{ $activo->modelo ?? '---' }}</span></td>
            <td><span class="s-label">Estado</span><span class="s-value">{{ $activo->estado ?? 'BUENO' }}</span></td>
        </tr>
        <tr>
            <td colspan="3" style="padding: 5px 6px;">
                <span class="s-label">Observaciones</span>
                <span class="s-value" style="font-weight: normal; font-size: 8.5pt;">{{ $activo->observaciones_activo ?? 'Sin observaciones registradas.' }}</span>
            </td>
        </tr>
    </table>

    {{-- Tabla de Movimientos --}}
    <table class="mov-table">
        <thead>
            <tr>
                <th width="18%">FECHA</th>
                <th width="41%">ENTREGA (ORIGEN)</th>
                <th width="41%" style="border-right: none;">RECIBE (DESTINO)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historial as $mov)
            <tr>
                <td class="date-text">{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $mov->entregador ? strtoupper($mov->entregador->nombre_completo) : 'UNIDAD DE COMPRAS' }}</strong><br>
                    <span class="sub-text">Emisor del activo</span>
                </td>
                <td style="border-right: none;">
                    <strong>{{ strtoupper($mov->receptor->nombre_completo) }}</strong><br>
                    <span class="sub-text">Responsable receptor</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} - Sistema de Inventarios
    </div>
@endsection