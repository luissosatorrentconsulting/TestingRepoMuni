<?php

namespace App\Filament\Resources\ActivoResource\RelationManagers;

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
                Forms\Components\TextInput::make('empleado.nombre_completo')
                    ->required()
                    ->maxLength(255),
            ]);
    }

  public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('id') // Cambiamos esto porque la asignación en sí no tiene "título"
        ->columns([
            // Mostramos el nombre del empleado que tiene el bien
            Tables\Columns\TextColumn::make('empleado.nombre_completo')
                ->label('Responsable')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('fecha_asignacion')
                ->label('Fecha de Entrega')
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('documento_respaldo')
                ->label('No. Acta')
                ->placeholder('Sin acta'),
                
            // Un estado para saber si es la asignación actual
            Tables\Columns\IconColumn::make('activo.es_baja')
                ->label('¿Sigue Activo?')
                ->boolean()
                ->falseIcon('heroicon-o-check-circle') // Si NO es baja, está bien
                ->trueIcon('heroicon-o-x-circle')
                ->falseColor('success')
                ->trueColor('danger'),
        ])
        ->filters([])
        ->headerActions([]) // No permitimos crear asignaciones desde aquí para no romper el flujo legal

->actions([
    // BOTÓN SEGURO: Navegar al empleado
    Tables\Actions\Action::make('ver_empleado')
        ->label('Ver Responsable')
        ->icon('heroicon-o-user')
        ->color('info')
        // Esta forma es mejor: llama directamente al Resource de Empleado
        ->url(fn ($record): string => \App\Filament\Resources\EmpleadoResource::getUrl('edit', ['record' => $record->empleado_id])),
    
    Tables\Actions\EditAction::make()->label('Corregir'),
])
        ->bulkActions([]);
}
}
