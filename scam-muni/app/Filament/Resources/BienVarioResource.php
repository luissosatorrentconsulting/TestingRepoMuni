<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BienVarioResource\Pages;
use App\Models\BienVario;
use App\Models\Municipalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

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
                Forms\Components\TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required(),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')
                    ->label('Categoría')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('marca_id')
                    ->label('Marca')
                    ->relationship('marcaInfo', 'nombre')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Marca')
                            ->required(),
                    ]),
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
                Forms\Components\Toggle::make('no_suma_inventario')
                    ->label('No suma a inventario')
                    ->helperText('Actívelo solo para excluir este bien del conteo total de inventario.')
                    ->default(false),
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
                Tables\Columns\TextColumn::make('marcaInfo.nombre')->label('Marca'),
                Tables\Columns\TextColumn::make('oficina.nombre')->label('Oficina'),
                Tables\Columns\TextColumn::make('estado')->badge(),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->headerActions([
                Tables\Actions\Action::make('exportarPdf')
                    ->label('Reporte de Inventario')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Tables\Contracts\HasTable $livewire) {
                        $bienes = $livewire->getFilteredTableQuery()->get();

                        $muni = Municipalidad::find(config('app.muni_id', 1));

                        $pdf = Pdf::loadView('pdf.bienes_varios_general', [
                            'bienes' => $bienes,
                            'muni' => $muni,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, "Bienes-Varios-" . now()->format('d-m-Y') . ".pdf");
                    }),
            ]);
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