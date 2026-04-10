<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OficinaResource\Pages;
use App\Filament\Resources\OficinaResource\RelationManagers;
use App\Models\Oficina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OficinaResource extends Resource
{
    protected static ?string $model = Oficina::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->maxLength(255),
            
            Forms\Components\Select::make('municipalidad_id')
                ->relationship('municipalidad', 'nombre')
                ->default(config('app.muni_id', 1)) // Tomar la muni por defecto
                ->required()
                ->searchable(),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('municipalidad.nombre')->label('Municipalidad'),
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
            'index' => Pages\ListOficinas::route('/'),
            'create' => Pages\CreateOficina::route('/create'),
            'edit' => Pages\EditOficina::route('/{record}/edit'),
        ];
    }
}
