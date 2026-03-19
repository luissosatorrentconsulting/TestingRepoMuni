<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
// AGREGAMOS ESTE USE QUE ES EL QUE FALTA
use App\Filament\Resources\EmpleadoResource\RelationManagers; 
use App\Models\Empleado;
use App\Models\Municipalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $modelLabel = 'Empleado';
    protected static ?string $pluralModelLabel = 'Empleados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Personal')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_completo')
                            ->label('Nombre Completo')
                            ->required(),
                        Forms\Components\TextInput::make('dpi')
                            ->label('DPI')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('puesto')
                            ->label('Cargo o Puesto')
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dpi')
                    ->label('DPI'),
                Tables\Columns\TextColumn::make('puesto')
                    ->label('Cargo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ACCIÓN: Generar el PDF de resguardo
                Tables\Actions\Action::make('resguardo')
                    ->label('Imprimir Resguardo')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->action(function (Empleado $record) {
                        $muni = Municipalidad::find(config('app.muni_id', 1));
                        
                        // Obtenemos solo las asignaciones de activos que NO han sido dados de baja
                        $asignaciones = $record->asignaciones()
                            ->whereHas('activo', function($query) {
                                $query->where('es_baja', false);
                            })
                            ->with('activo')
                            ->get();

                        $pdf = Pdf::loadView('pdf.resguardo_empleado', [
                            'empleado' => $record,
                            'asignaciones' => $asignaciones,
                            'muni' => $muni,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, "Resguardo-{$record->nombre_completo}.pdf");
                    }),
            ]);
    }

    // REGISTRO DE LA RELACIÓN PARA VER LOS BIENES EN PANTALLA
    public static function getRelations(): array
    {
        return [
            RelationManagers\AsignacionesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}