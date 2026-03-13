<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('prefijo')
                    ->label('Prefijo (ej. MOB)')
                    ->required()
                    ->maxLength(10),
                TextInput::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->required(),
                TextInput::make('porcentaje_depreciacion')
                    ->label('% Depreciación Anual')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prefijo')->label('Prefijo'),
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('porcentaje_depreciacion')->label('% Depreciación'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
        ];
    }
}