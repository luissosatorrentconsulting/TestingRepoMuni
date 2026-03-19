<?php

namespace App\Filament\Resources\EmpleadoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AsignacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'asignaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('activo.descripcion')
                    ->required()
                    ->maxLength(255),
            ]);
    }

   public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('activo.descripcion')
   ->columns([
    Tables\Columns\TextColumn::make('activo.codigo_etiqueta')->label('Código Inv.'),
    Tables\Columns\TextColumn::make('activo.descripcion')->label('Bien / Equipo'),
    Tables\Columns\TextColumn::make('fecha_asignacion')->label('Desde el:')->date('d/m/Y'),
])
->actions([
    // BOTÓN SEGURO: Navegar al activo
    Tables\Actions\Action::make('ver_activo')
        ->label('Ver Bien')
        ->icon('heroicon-o-archive-box')
        ->color('info')
        // Llamamos al Resource de Activo para obtener su URL de edición
        ->url(fn ($record): string => \App\Filament\Resources\ActivoResource::getUrl('edit', ['record' => $record->activo_id])),
    
    Tables\Actions\EditAction::make()->label('Corregir'),
])
        ->filters([
            // Filtro para ver solo lo que NO es baja
            Tables\Filters\TernaryFilter::make('solo_activos')
                ->label('Estado')
                ->placeholder('Todos los bienes')
                ->trueLabel('Solo Activos Vigentes')
                ->falseLabel('Incluir Bajas')
                ->queries(
                    true: fn ($query) => $query->whereHas('activo', fn($q) => $q->where('es_baja', false)),
                    false: fn ($query) => $query->all(),
                ),
        ]);
}
}


