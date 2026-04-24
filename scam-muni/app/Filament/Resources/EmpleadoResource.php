<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Filament\Resources\EmpleadoResource\RelationManagers; 
use App\Models\Empleado;
use App\Models\Municipalidad;
use App\Models\Activo;
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
                        
                        Forms\Components\Select::make('puesto_id')
                            ->relationship('puesto_oficial', 'nombre')
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
                
                Tables\Columns\TextColumn::make('puesto_oficial.nombre')
                    ->label('Cargo Oficial')
                    ->description(fn (Empleado $record): string => $record->puesto_oficial?->departamento?->nombre ?? 'Sin Departamento'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // BOTÓN ACTUALIZADO PARA VISOR DE IMPRESIÓN
                Tables\Actions\Action::make('imprimirResguardo')
                    ->label('Imprimir Resguardo')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (Empleado $record): string => route('empleado.resguardo.pdf', $record))
                    ->openUrlInNewTab(),
                    Tables\Actions\Action::make('imprimirTraslados')
        ->label('Historial Traslados')
        ->icon('heroicon-o-arrows-right-left')
        ->color('info')
        ->url(fn (Empleado $record): string => route('empleado.traslados.pdf', $record))
        ->openUrlInNewTab(),
                    
            ]);
    }

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