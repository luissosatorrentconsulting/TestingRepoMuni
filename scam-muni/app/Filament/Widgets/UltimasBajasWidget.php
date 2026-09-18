<?php

namespace App\Filament\Widgets;

use App\Models\Activo;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UltimasBajasWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimas Bajas')
            ->query(
                Activo::query()
                    ->where('es_baja', true)
                    ->with('categoria')
                    ->latest('fecha_baja')
            )
            ->columns([
                Tables\Columns\TextColumn::make('fecha_baja')
                    ->label('Fecha de Baja')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('codigo_etiqueta')
                    ->label('Código'),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(35),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('costo_original')
                    ->label('Costo')
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('motivo_baja')
                    ->label('Motivo')
                    ->limit(40)
                    ->placeholder('Sin motivo registrado'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
