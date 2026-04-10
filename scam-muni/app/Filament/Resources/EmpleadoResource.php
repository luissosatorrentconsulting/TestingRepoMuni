<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
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
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('dpi')
                            ->label('DPI')
                            ->required(),
                        
                        // INTEGRACIÓN: El nuevo selector de Puesto Jerárquico
                        Forms\Components\Select::make('puesto_id')
                            ->relationship('puesto_oficial', 'nombre') // Usamos el nombre de la relación del modelo
                            ->label('Puesto Oficial (Estructura)')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([ 
                                Forms\Components\TextInput::make('nombre')->required(),
                                Forms\Components\Select::make('departamento_id')
                                    ->relationship('departamento', 'nombre')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Toggle::make('activo')
                            ->default(true),
                    ])->columns(2),
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
                
                // CAMBIO: Ahora mostramos el nombre desde la relación
                Tables\Columns\TextColumn::make('puesto_oficial.nombre')
                    ->label('Cargo Oficial')
                    ->description(fn (Empleado $record): string => $record->puesto_oficial?->departamento?->nombre ?? 'Sin Departamento'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // TU ACCIÓN ORIGINAL: Generar el PDF (Intacta)
                Tables\Actions\Action::make('resguardo')
                    ->label('Imprimir Resguardo')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->action(function (Empleado $record) {
                        $muni = Municipalidad::find(config('app.muni_id', 1));
                        
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

    public static function getRelations(): array
    {
        return [
            // TU RELATION MANAGER ORIGINAL: (Intacto)
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