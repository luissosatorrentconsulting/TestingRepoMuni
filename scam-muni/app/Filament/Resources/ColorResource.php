<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColorResource\Pages;
use App\Filament\Resources\ColorResource\RelationManagers;
use App\Models\Color;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Color';
protected static ?string $pluralModelLabel = 'Colores';
protected static ?string $navigationLabel = 'Colores';

    public static function form(Form $form): Form
    {
        return $form->schema([
    Forms\Components\TextInput::make('nombre')->required(),
]);
    }

    public static function table(Table $table): Table
    {
        

            return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')
                ->label('Nombre del Color')
                ->sortable()
                ->searchable(),
            
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de Creación')
                ->dateTime()
                ->sortable(),
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
            'index' => Pages\ListColors::route('/'),
            'create' => Pages\CreateColor::route('/create'),
            'edit' => Pages\EditColor::route('/{record}/edit'),
        ];
    }
}
