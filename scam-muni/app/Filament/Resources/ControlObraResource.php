<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControlObraResource\Pages;
use App\Models\ControlObra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ControlObraResource extends Resource
{
    protected static ?string $model = ControlObra::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Infraestructura';
    protected static ?string $pluralModelLabel = 'Control de Obras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('snip')
                    ->label('SNIP (Código Único)')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nombre_proyecto')
                    ->label('Nombre del Proyecto')
                    ->required(),
                Forms\Components\TextInput::make('numero_contrato')
                    ->label('No. De Contrato'),
                Forms\Components\TextInput::make('monto')
                    ->numeric()
                    ->prefix('Q')
                    ->label('Monto'),
                Forms\Components\DatePicker::make('fecha')
                    ->label('Fecha'),
                Forms\Components\Select::make('municipalidad_id')
                    ->relationship('municipalidad', 'nombre')
                    ->label('Municipalidad')
                    ->default(config('app.muni_id', 1))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('snip')->label('Código SNIP')->searchable(),
                Tables\Columns\TextColumn::make('nombre_proyecto')->label('Proyecto')->searchable(),
                Tables\Columns\TextColumn::make('monto')->money('GTQ')->label('Monto'),
                Tables\Columns\TextColumn::make('fecha')->date()->label('Fecha'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListControlObras::route('/'),
            'create' => Pages\CreateControlObra::route('/create'),
            'edit' => Pages\EditControlObra::route('/{record}/edit'),
        ];
    }
}