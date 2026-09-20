<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionResource\Pages;
use App\Models\Asignacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AsignacionResource extends Resource
{
    protected static ?string $model = Asignacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $modelLabel = 'Asignación';
    protected static ?string $pluralModelLabel = 'Asignaciones';
    protected static ?string $navigationLabel = 'Asignaciones de Equipo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la Entrega')
                    ->description('Vincule un activo con un empleado responsable.')
                    ->schema([
                        // Solo mostrará activos de MI municipalidad gracias al GlobalScope,
                        // y excluye los que ya están de baja o tienen una asignación vigente
                        // (para no poder "reasignar" por accidente desde este formulario).
                        // Tampoco se puede cambiar al editar, por la misma razón que el
                        // empleado: cambiar de bien es una reasignación, no una corrección.
                        Forms\Components\Select::make('activo_id')
                            ->relationship(
                                'activo',
                                'descripcion',
                                modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query, $livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord
                                    ? $query->disponibles()
                                    : $query,
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo_etiqueta} - {$record->descripcion}")
                            ->label('Activo / Bien')
                            ->required()
                            ->searchable(['descripcion', 'codigo_etiqueta'])
                            ->preload()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (string $operation): bool => $operation !== 'edit'),

                        // Solo mostrará empleados de MI municipalidad. No se
                        // puede cambiar al editar: eso sería una reasignación
                        // "silenciosa" sin pasar por el flujo de Reasignar
                        // (que cierra la asignación anterior y deja registro
                        // en el historial de movimientos).
                        Forms\Components\Select::make('empleado_id')
                            ->relationship('empleado', 'nombre_completo')
                            ->label('Empleado Responsable')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (string $operation): bool => $operation !== 'edit')
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Para cambiar de responsable usá el botón "Reasignar" en el listado.'
                                : null),

                        Forms\Components\DatePicker::make('fecha_asignacion')
                            ->label('Fecha de Asignación')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('documento_respaldo')
                            ->label('No. de Acta')
                            ->placeholder('Ej: ACTA-001-2026')
                            ->required(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Motivo / Observaciones')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activo.codigo_etiqueta')->label('Código')->searchable(),
                Tables\Columns\TextColumn::make('activo.descripcion')->label('Activo')->searchable(),
                Tables\Columns\TextColumn::make('empleado.nombre_completo')->label('Responsable')->searchable(),
                Tables\Columns\TextColumn::make('fecha_asignacion')->label('Fecha')->date('d/m/Y'),
                Tables\Columns\IconColumn::make('activa')->label('Vigente')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activa')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo vigentes')
                    ->falseLabel('Solo históricas'),

                Tables\Filters\TrashedFilter::make(),
            ])
         ->actions([
    Tables\Actions\EditAction::make()->label('Corregir'),
    Tables\Actions\RestoreAction::make(),
    Tables\Actions\ForceDeleteAction::make(),

    Tables\Actions\Action::make('reasignar')
        ->label('Reasignar')
        ->icon('heroicon-o-arrow-path-rounded-square')
        ->color('warning')
        ->visible(fn (Asignacion $record): bool => (bool) $record->activa)
        ->form([
            Forms\Components\Select::make('empleado_id')
                ->relationship('empleado', 'nombre_completo')
                ->label('Nuevo Responsable')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\DatePicker::make('fecha_asignacion')
                ->label('Fecha de Reasignación')
                ->default(now())
                ->required(),

            Forms\Components\TextInput::make('documento_respaldo')
                ->label('No. de Acta')
                ->required(),

            Forms\Components\Textarea::make('observaciones')
                ->label('Motivo de la Reasignación')
                ->required()
                ->columnSpanFull(),
        ])
        ->action(function (Asignacion $record, array $data): void {
            Asignacion::create([
                'activo_id' => $record->activo_id,
                'empleado_id' => $data['empleado_id'],
                'fecha_asignacion' => $data['fecha_asignacion'],
                'documento_respaldo' => $data['documento_respaldo'],
                'observaciones' => $data['observaciones'],
            ]);
        }),

    Tables\Actions\Action::make('pdf')
        ->label('Imprimir Acta')
        ->icon('heroicon-o-printer')
        ->color('info')
        ->modalHeading('Acta de Asignación')
        ->modalContent(fn (Asignacion $record) => view('filament.modals.pdf-viewer', [
            'url' => route('asignacion.acta.pdf', $record),
        ]))
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Cerrar')
        ->modalWidth('7xl'),
        
])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ]);

            
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsignacions::route('/'),
            'create' => Pages\CreateAsignacion::route('/create'),
            'edit' => Pages\EditAsignacion::route('/{record}/edit'),
        ];
    }
}