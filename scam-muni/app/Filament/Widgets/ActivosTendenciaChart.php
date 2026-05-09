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
    $data = Activo::query()
        ->select(
            DB::raw('COUNT(*) as count'), 
            DB::raw("DATE_FORMAT(created_at, '%M') as month"),
            DB::raw("MIN(created_at) as first_of_month") // Usamos esto para ordenar cronológicamente
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('first_of_month', 'asc') // Ahora sí es compatible
        ->pluck('count', 'month');

    return [
        'datasets' => [
            [
                'label' => 'Activos ingresados',
                'data' => $data->values()->toArray(),
                'fill' => 'start',
                'borderColor' => '#3b82f6',
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