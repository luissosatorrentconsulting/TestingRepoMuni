<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuestoResource\Pages;
use App\Models\Puesto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PuestoResource extends Resource
{
    protected static ?string $model = Puesto::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Recursos Humanos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del Puesto')
                    ->required(),

                // CORREGIDO: El puesto ahora selecciona la Oficina de forma directa
                Forms\Components\Select::make('oficina_id')
                    ->relationship('oficina', 'nombre')
                    ->label('Oficina Perteneciente')
                    ->required()
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('empleado_jefe_id')
                    ->relationship('jefe', 'nombre_completo')
                    ->label('Jefe Responsable (Empleado)')
                    ->placeholder('Seleccione al jefe de este puesto')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Puesto')
                    ->searchable()
                    ->sortable(),

                // CORREGIDO: Muestra la Oficina vinculada
                Tables\Columns\TextColumn::make('oficina.nombre')
                    ->label('Oficina')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jefe.nombre_completo')
                    ->label('Jefe Responsable')
                    ->placeholder('Sin jefe asignado'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPuestos::route('/'),
            'create' => Pages\CreatePuesto::route('/create'),
            'edit' => Pages\EditPuesto::route('/{record}/edit'),
        ];
    }
}