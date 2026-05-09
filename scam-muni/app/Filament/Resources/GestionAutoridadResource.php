<?php
namespace App\Filament\Resources;

use App\Filament\Resources\GestionAutoridadResource\Pages;
use App\Models\GestionAutoridad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class GestionAutoridadResource extends Resource
{
    protected static ?string $model = GestionAutoridad::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Gestión de Autoridad';
    protected static ?string $pluralModelLabel = 'Gestiones de Autoridades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cargo')
                    ->description('Asigne un empleado a un cargo de autoridad para las firmas de los reportes.')
                    ->schema([
                        Select::make('empleado_id')
                            ->relationship('empleado', 'nombre_completo')
                            ->label('Seleccionar Empleado')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('cargo')
                            ->label('Cargo de Autoridad')
                            ->options([
                                'ALCALDE' => 'Alcalde Municipal',
                                'DIRECTOR_FINANCIERO' => 'Director Financiero (DAFIM)',
                            ])
                            ->required(),

                        TextInput::make('periodo_gestion')
                            ->label('Periodo de Gestión')
                            ->placeholder('Ej: 2024-2028')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Vigencia del Cargo')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Toma de Posesión')
                            ->default(now())
                            ->required(),

                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Cierre de Gestión')
                            ->helperText('Dejar vacío si es el cargo actual.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('empleado.nombre_completo')
                    ->label('Funcionario')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ALCALDE' => 'Alcalde Municipal',
                        'DIRECTOR_FINANCIERO' => 'Director Financiero',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ALCALDE' => 'info',
                        'DIRECTOR_FINANCIERO' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('periodo_gestion')
                    ->label('Periodo')
                    ->searchable(),

                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y'),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Gestión Actual'),
            ])
            ->filters([
                // Aquí podrías filtrar por cargo si la lista crece
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGestionAutoridads::route('/'),
            'create' => Pages\CreateGestionAutoridad::route('/create'),
            'edit' => Pages\EditGestionAutoridad::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
{
    // Solo el administrador tiene permiso para ver este recurso
    return auth()->user()?->rol === 'admin';
}
}