<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionResource\Pages;
use App\Models\Asignacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Municipalidad;

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
                        // Solo mostrará activos de MI municipalidad gracias al GlobalScope
                        Forms\Components\Select::make('activo_id')
                            ->relationship('activo', 'descripcion')
                            ->label('Activo / Bien')
                            ->required()
                            ->searchable()
                            ->preload(),

                        // Solo mostrará empleados de MI municipalidad
                        Forms\Components\Select::make('empleado_id')
                            ->relationship('empleado', 'nombre_completo')
                            ->label('Empleado Responsable')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('fecha_asignacion')
                            ->label('Fecha de Asignación')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('documento_respaldo')
                            ->label('No. de Acta')
                            ->placeholder('Ej: ACTA-001-2026'),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones del Estado')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activo.codigo_etiqueta')->label('Código'),
                Tables\Columns\TextColumn::make('activo.descripcion')->label('Activo'),
                Tables\Columns\TextColumn::make('empleado.nombre_completo')->label('Responsable'),
                Tables\Columns\TextColumn::make('fecha_asignacion')->label('Fecha')->date('d/m/Y'),
            ])
         ->actions([
    Tables\Actions\EditAction::make(),
    
    Tables\Actions\Action::make('pdf')
        ->label('Imprimir Acta')
        ->icon('heroicon-o-printer')
        ->color('info')
        ->action(function (Asignacion $record) {
            $muni = Municipalidad::find(config('app.muni_id', 1));
            
            $pdf = Pdf::loadView('pdf.acta_asignacion', [
                'asignacion' => $record,
                'muni' => $muni,
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, "Acta-{$record->id}.pdf");
        }),
        
])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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