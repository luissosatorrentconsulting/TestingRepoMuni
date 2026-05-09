<?php

namespace App\Filament\Widgets;

use App\Models\Activo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Activos', Activo::count())
                ->description('Bienes registrados en total')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Inversión Total', 'Q ' . number_format(Activo::sum('costo_original'), 2))
                ->description('Costo acumulado del patrimonio')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Bienes de Baja', Activo::where('es_baja', true)->count())
                ->description('Activos fuera de servicio')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}