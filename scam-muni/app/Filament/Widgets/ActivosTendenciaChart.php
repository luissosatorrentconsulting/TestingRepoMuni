<?php

namespace App\Filament\Widgets;
use Illuminate\Support\Facades\DB;

use App\Models\Activo;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ActivosTendenciaChart extends ChartWidget
{
    protected static ?string $heading = 'Registros de Activos (Últimos 6 meses)';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Esta parte cuenta los registros creados por mes
        // Nota: Si no tienes instalada la librería "Trend", podemos usar una consulta simple:
        $data = Activo::query()
            ->select(DB::raw('COUNT(*) as count'), DB::raw("DATE_FORMAT(created_at, '%M') as month"))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('created_at')
            ->pluck('count', 'month');

        return [
            'datasets' => [
                [
                    'label' => 'Activos ingresados',
                    'data' => $data->values()->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#3b82f6', // Color de la línea (Azul)
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Aquí definimos que sea de LÍNEA
    }
}