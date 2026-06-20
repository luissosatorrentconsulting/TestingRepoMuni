<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BienVarioResource\Pages;
use App\Models\BienVario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BienVarioResource extends Resource
{
    protected static ?string $model = BienVario::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $pluralModelLabel = 'Bienes Varios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_qr')
                    ->label('Código QR'),
                Forms\Components\TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required(),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')
                    ->label('Categoría')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('marca')
                    ->label('Marca'),
                Forms\Components\TextInput::make('costo')
                    ->numeric()
                    ->prefix('Q')
                    ->label('Costo Original'),
                Forms\Components\DatePicker::make('fecha_compra')
                    ->label('Fecha de Compra'),
                Forms\Components\TextInput::make('numero_factura')
                    ->label('No. Factura'),
                Forms\Components\Select::make('proveedor_id')
                    ->relationship('proveedor', 'nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('numero_inventario')
                    ->label('No. Inventariado'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'Excelente' => 'Excelente',
                        'Bueno' => 'Bueno',
                        'Regular' => 'Regular',
                        'Malo' => 'Malo',
                    ])
                    ->default('Excelente')
                    ->required(),
                Forms\Components\Select::make('oficina_id')
                    ->relationship('oficina', 'nombre')
                    ->label('Oficina Responsable')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('fecha_baja')
                    ->label('Fecha de Baja'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_inventario')->label('No. Inventario')->searchable(),
                Tables\Columns\TextColumn::make('descripcion')->label('Descripción')->searchable(),
                Tables\Columns\TextColumn::make('categoria.nombre')->label('Categoría'),
                Tables\Columns\TextColumn::make('oficina.nombre')->label('Oficina'),
                Tables\Columns\TextColumn::make('estado')->badge(),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBienVarios::route('/'),
            'create' => Pages\CreateBienVario::route('/create'),
            'edit' => Pages\EditBienVario::route('/{record}/edit'),
        ];
    }
}