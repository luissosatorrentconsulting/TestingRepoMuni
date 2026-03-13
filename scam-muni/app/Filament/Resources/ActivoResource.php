<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivoResource\Pages;
use App\Models\Activo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class ActivoResource extends Resource
{
    protected static ?string $model = Activo::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    // Personalización en español
    protected static ?string $modelLabel = 'Activo';
    protected static ?string $pluralModelLabel = 'Inventario de Activos';
    protected static ?string $navigationLabel = 'Inventario de Activos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECCIÓN 1: CLASIFICACIÓN (Multi-Muni Invisible)
                Forms\Components\Section::make('Clasificación')
                    ->description('Defina la categoría del bien.')
                    ->schema([
                        Select::make('categoria_id')
                            ->relationship('categoria', 'nombre')
                            ->label('Categoría del Activo')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('codigo_etiqueta')
                            ->label('Código Generado (Auto)')
                            ->placeholder('Se generará al guardar')
                            ->disabled()
                            ->dehydrated(false), // No enviamos este campo, el modelo lo genera
                    ])->columns(2),

                // SECCIÓN 2: DETALLES TÉCNICOS
                Forms\Components\Section::make('Detalles del Bien')
                    ->schema([
                        TextInput::make('descripcion')
                            ->label('Descripción Completa')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('marca')->label('Marca'),
                        TextInput::make('modelo')->label('Modelo'),
                        TextInput::make('serie')->label('No. de Serie'),
                    ])->columns(3),

                // SECCIÓN 3: DATOS FINANCIEROS
                Forms\Components\Section::make('Información de Adquisición')
                    ->schema([
                        TextInput::make('costo_original')
                            ->label('Costo Original')
                            ->numeric()
                            ->prefix('Q')
                            ->required(),

                        DatePicker::make('fecha_compra')
                            ->label('Fecha de Adquisición')
                            ->default(now())
                            ->required(),

                        TextInput::make('valor_desecho')
                            ->label('Valor de Desecho')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_etiqueta')
                    ->label('Código QR')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable(),

                TextColumn::make('costo_original')
                    ->label('Costo')
                    ->money('GTQ')
                    ->sortable(),

                TextColumn::make('fecha_compra')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre')
                    ->label('Filtrar por Categoría'),
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
            // Aquí agregaremos el historial de asignaciones en la Fase 3
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivos::route('/'),
            'create' => Pages\CreateActivo::route('/create'),
            'edit' => Pages\EditActivo::route('/{record}/edit'),
        ];
    }
}