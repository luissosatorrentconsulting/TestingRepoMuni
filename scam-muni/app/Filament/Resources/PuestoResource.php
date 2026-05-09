<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PuestoResource\Pages;
use App\Filament\Resources\PuestoResource\RelationManagers;
use App\Models\Puesto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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

            Forms\Components\Select::make('departamento_id')
                ->relationship('departamento', 'nombre')
                ->label('Departamento')
                ->required()
                ->preload()
                ->searchable(),

            // SELECTOR DE JEFE (Persona)
            Forms\Components\Select::make('empleado_jefe_id')
                ->relationship('jefe', 'nombre_completo') // Usa el campo que tengas para el nombre
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
            // 1. Mostrar el nombre del puesto
            Tables\Columns\TextColumn::make('nombre')
                ->label('Nombre del Puesto')
                ->searchable()
                ->sortable(),

            // 2. Mostrar el departamento (usando la relación)
            Tables\Columns\TextColumn::make('departamento.nombre')
                ->label('Departamento')
                ->sortable(),

            // 3. Mostrar el jefe (usando la relación con Empleado)
            Tables\Columns\TextColumn::make('jefe.nombre_completo') // Verifica si tu campo es nombre_completo o solo nombre
                ->label('Jefe Responsable')
                ->placeholder('Sin jefe asignado'),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
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
