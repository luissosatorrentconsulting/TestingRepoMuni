<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivoResource\Pages;
// AGREGAMOS ESTA LÍNEA PARA QUE ENCUENTRE EL RELATION MANAGER
use App\Filament\Resources\ActivoResource\RelationManagers; 
use App\Models\Activo;
use App\Models\Municipalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Colors\Color;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class ActivoResource extends Resource
{
    protected static ?string $model = Activo::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $modelLabel = 'Activo';
    protected static ?string $pluralModelLabel = 'Inventario de Activos';
    protected static ?string $navigationLabel = 'Inventario de Activos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                            ->dehydrated(false), 
                    ])->columns(2),

Forms\Components\Section::make('Detalles del Bien')
    ->schema([
        TextInput::make('descripcion')
            ->label('Descripción Completa')
            ->required()
            ->columnSpanFull(),

        TextInput::make('marca')->label('Marca'),
        TextInput::make('modelo')->label('Modelo'),
        TextInput::make('serie')->label('No. de Serie'),

        // Agregados correctamente dentro de la sección
        Forms\Components\Select::make('estado')
            ->label('Estado Físico')
            ->options([
                'BUENO' => 'Bueno',
                'REGULAR' => 'Regular',
                'MALO' => 'Malo',
            ])
            ->default('BUENO'),

        TextInput::make('color')
            ->label('Color'),

        Forms\Components\Textarea::make('observaciones_activo')
            ->label('Observaciones del Bien')
            ->columnSpanFull(), // Para que use todo el ancho
    ])->columns(3),


                    

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

                Tables\Columns\TextColumn::make('asignaciones.empleado.nombre_completo')
                    ->label('Responsable Actual')
                    ->placeholder('En Bodega')
                    ->listWithLineBreaks()
                    ->limitList(1),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre')
                    ->label('Filtrar por Categoría'),

                Tables\Filters\TernaryFilter::make('es_baja')
                    ->label('Estado del Bien')
                    ->placeholder('Activos Disponibles')
                    ->trueLabel('Ver solo Bajas')
                    ->falseLabel('Ver solo Activos Vigentes')
                    ->queries(
                        true: fn (Builder $query) => $query->where('es_baja', true),
                        false: fn (Builder $query) => $query->where('es_baja', false),
                        blank: fn (Builder $query) => $query->where('es_baja', false),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

               Tables\Actions\Action::make('historial')
    ->label('Historial')
    ->icon('heroicon-o-clock')
    ->color('info')
    // Esto genera el link dinámico a la ruta que acabas de crear
    ->url(fn (Activo $record): string => route('activos.historial.pdf', $record))
    // Esto obliga al navegador a abrirlo en una pestaña nueva
    ->openUrlInNewTab(),
                
                Tables\Actions\Action::make('baja')
                    ->label('Dar de Baja')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Finalizar vida útil del bien')
                    ->form([
                        Forms\Components\Textarea::make('motivo_baja')
                            ->label('Motivo de la baja')
                            ->required(),
                    ])
                    ->action(function (Activo $record, array $data): void {
                        $record->update([
                            'es_baja' => true,
                            'descripcion' => $record->descripcion . " (DE BAJA: " . $data['motivo_baja'] . ")",
                        ]);
                    })
                    ->visible(fn (Activo $record): bool => !$record->es_baja),
                   
                Tables\Actions\Action::make('reactivar')
                    ->label('Reactivar Bien')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Activo $record): void {
                        $record->update([
                            'es_baja' => false,
                        ]);
                    })
                    ->visible(fn (Activo $record): bool => $record->es_baja), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportarPdf')
                    ->label('Reporte de Inventario')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Tables\Contracts\HasTable $livewire) {
                        $activos = $livewire->getFilteredTableQuery()->get();
                        
                        $muni = Municipalidad::find(config('app.muni_id', 1));

                        $pdf = Pdf::loadView('pdf.inventario_general', [
                            'activos' => $activos,
                            'muni' => $muni,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, "Inventario-" . now()->format('d-m-Y') . ".pdf");
                    }),
            ]);
    }

    // SOLO UNA VEZ getRelations()
    public static function getRelations(): array
    {
        return [
            RelationManagers\AsignacionesRelationManager::class,
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