<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OficinaResource\Pages;
use App\Models\Oficina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OficinaResource extends Resource
{
    protected static ?string $model = Oficina::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Recursos Humanos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Select::make('departamento_id')
                    ->relationship('departamento', 'nombre')
                    ->label('Departamento / Dirección')
                    ->required()
                    ->searchable()
                    ->preload(),

                // CORREGIDO: Apunta a ubicacionPrincipal (relación BelongsTo) para guardar un entero limpio sin [ ]
                Forms\Components\Select::make('ubicacion_id')
                    ->relationship(
                        name: 'ubicacionPrincipal', 
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn ($query, $record) => $record 
                            ? $query->where('oficina_id', $record->id) // Si editamos, solo muestra las de esta oficina
                            : $query // Si es nueva, permite buscarlas todas
                    )
                    ->label('Ubicación Física Principal')
                    ->placeholder('Crea ubicaciones primero para asignarlas aquí')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('departamento.nombre')->label('Departamento / Dirección'),
                Tables\Columns\TextColumn::make('departamento.municipalidad.nombre')->label('Municipalidad'),
                // Agregado extra para ver la ubicación actual en la lista
                Tables\Columns\TextColumn::make('ubicacionPrincipal.nombre')->label('Ubicación Principal')->default('No asignada'),
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
            'index' => Pages\ListOficinas::route('/'),
            'create' => Pages\CreateOficina::route('/create'),
            'edit' => Pages\EditOficina::route('/{record}/edit'),
        ];
    }
}