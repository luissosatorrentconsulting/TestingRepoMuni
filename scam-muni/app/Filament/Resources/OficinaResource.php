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

                // Selector directo: Guarda el ID de la ubicación en el campo 'ubicacion_id' de la oficina
                Forms\Components\Select::make('ubicacion_id')
                    ->relationship('ubicacionPrincipal', 'nombre')
                    ->label('Ubicación Física Principal')
                    ->placeholder('Selecciona el área física asignada')
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
                Tables\Columns\TextColumn::make('ubicacionPrincipal.nombre')->label('Ubicación Física')->default('No asignada'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
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