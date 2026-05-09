<?php

namespace App\Filament\Widgets;

use App\Models\Activo;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB; // <--- VITAL PARA QUE NO DE ERROR

class ActivosPorCategoriaChart extends ChartWidget
{
    protected static ?string $heading = 'Activos por Categoría';

    // Esto lo pone a la par del de tendencia (mitad de pantalla)
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // 1. Hacemos el Join con la tabla de categorías
        // Nota: Asegúrate de que tu tabla se llame 'categorias' y tenga la columna 'nombre'
        $data = Activo::query()
            ->join('categorias', 'activos.categoria_id', '=', 'categorias.id')
            ->select('categorias.nombre', DB::raw('count(*) as total'))
            ->groupBy('categorias.nombre')
            ->pluck('total', 'nombre');

        // Si por alguna razón no hay datos, enviamos arrays vacíos
        if ($data->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Categorías',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#f59e0b', // Ámbar
                        '#10b981', // Esmeralda
                        '#3b82f6', // Azul
                        '#ef4444', // Rojo
                        '#8b5cf6', // Violeta
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Tipo Pastel
    }
}