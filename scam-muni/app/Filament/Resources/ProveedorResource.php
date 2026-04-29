<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Filament\Resources\ProveedorResource\RelationManagers;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Proveedor';
protected static ?string $pluralModelLabel = 'Proveedores';
protected static ?string $navigationLabel = 'Proveedores';

    public static function form(Form $form): Form
    {
        return $form->schema([
    Forms\Components\TextInput::make('nombre')->required(),
    Forms\Components\TextInput::make('nit'),
    Forms\Components\TextInput::make('telefono')->tel(),
    Forms\Components\TextInput::make('direccion'),
]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')
                ->label('Proveedor')
                ->searchable(),
            Tables\Columns\TextColumn::make('nit')
                ->label('NIT'),
            Tables\Columns\TextColumn::make('telefono')
                ->label('Teléfono'),
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
            'index' => Pages\ListProveedors::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'edit' => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }
}
