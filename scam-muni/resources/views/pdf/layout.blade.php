<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 100px 25px; }
        header { position: fixed; top: -80px; left: 0; right: 0; height: 100px; text-align: center; border-bottom: 1px solid #000; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; text-align: center; font-size: 10px; border-top: 1px solid #ccc; }
        body { font-family: sans-serif; font-size: 12px; margin-top: 20px; }
        .titulo { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
        <strong>{{ $muni->nombre }}</strong><br>
        Sistema de Control de Inventarios<br>
        NIT: {{ $muni->nit }}<br>
        <small>Fecha de Impresión: {{ now()->format('d/m/Y H:i') }}</small>
    </header>

    <footer>
        Página <script type="text/php">if (isset($pdf)) { echo $fontMetrics->get_page_number($pdf); }</script>
    </footer>

    <main>
        <div class="titulo">@yield('titulo_reporte')</div>
        @yield('contenido')
    </main>
</body>
</html>