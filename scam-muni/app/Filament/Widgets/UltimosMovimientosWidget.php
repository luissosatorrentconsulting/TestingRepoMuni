<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoActivo;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UltimosMovimientosWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimos Movimientos')
            ->query(
                MovimientoActivo::query()
                    ->with(['activo', 'entregador', 'receptor'])
                    ->latest('fecha_movimiento')
            )
            ->columns([
                Tables\Columns\TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('activo.codigo_etiqueta')
                    ->label('Código'),
                Tables\Columns\TextColumn::make('activo.descripcion')
                    ->label('Bien')
                    ->limit(35),
                Tables\Columns\TextColumn::make('entregador.nombre_completo')
                    ->label('Entrega')
                    ->placeholder('Compra / Ingreso nuevo'),
                Tables\Columns\TextColumn::make('receptor.nombre_completo')
                    ->label('Recibe'),
                Tables\Columns\TextColumn::make('tipo_movimiento')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => 'compra',
                        'info' => 'traslado',
                    ]),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
