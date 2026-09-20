<?php

namespace App\Filament\Resources\ActivoResource\RelationManagers;

use App\Models\Asignacion;
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
        // Solo permite corregir datos de la asignación en sí (acta, fecha,
        // observaciones). Cambiar el bien o el responsable es una
        // reasignación, no una corrección: eso se hace con "Reasignar".
        // (Antes este formulario editaba 'empleado.nombre_completo', que en
        // realidad le cambiaba el nombre al Empleado en todo el sistema.)
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->label('Fecha de Asignación')
                    ->required(),

                Forms\Components\TextInput::make('documento_respaldo')
                    ->label('No. de Acta')
                    ->placeholder('Ej: ACTA-001-2026'),

                Forms\Components\Textarea::make('observaciones')
                    ->label('Motivo / Observaciones')
                    ->columnSpanFull(),
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

            Tables\Columns\IconColumn::make('activa')
                ->label('Vigente')
                ->boolean(),

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

    Tables\Actions\Action::make('reasignar')
        ->label('Reasignar')
        ->icon('heroicon-o-arrow-path-rounded-square')
        ->color('warning')
        ->visible(fn (Asignacion $record): bool => (bool) $record->activa)
        ->form([
            Forms\Components\Select::make('empleado_id')
                ->relationship('empleado', 'nombre_completo')
                ->label('Nuevo Responsable')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\DatePicker::make('fecha_asignacion')
                ->label('Fecha de Reasignación')
                ->default(now())
                ->required(),

            Forms\Components\TextInput::make('documento_respaldo')
                ->label('No. de Acta')
                ->required(),

            Forms\Components\Textarea::make('observaciones')
                ->label('Motivo de la Reasignación')
                ->required()
                ->columnSpanFull(),
        ])
        ->action(function (Asignacion $record, array $data): void {
            Asignacion::create([
                'activo_id' => $record->activo_id,
                'empleado_id' => $data['empleado_id'],
                'fecha_asignacion' => $data['fecha_asignacion'],
                'documento_respaldo' => $data['documento_respaldo'],
                'observaciones' => $data['observaciones'],
            ]);
        }),
])
        ->bulkActions([]);
}
}
