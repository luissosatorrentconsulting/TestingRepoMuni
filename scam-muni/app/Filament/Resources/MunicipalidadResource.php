<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MunicipalidadResource\Pages; // <--- ESTA LÍNEA ES CLAVE
use App\Models\Municipalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;

class MunicipalidadResource extends Resource
{
 
    protected static ?string $model = Municipalidad::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Municipalidad';
    protected static ?string $pluralModelLabel = 'Municipalidades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('codigo_muni')
                    ->label('Código (ej. 910)')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('nombre')
                    ->label('Nombre de la Municipalidad')
                    ->required(),
                TextInput::make('nit')
                    ->label('NIT'),
                    TextInput::make('departamento')
    ->label('Departamento Geográfico')
    ->placeholder('Ej. QUETZALTENANGO')
    ->required(),
FileUpload::make('logo')
    ->label('Logo Institucional (PNG)')
    ->image() // Valida que sea imagen
    ->directory('logos-muni') // Se guardará en storage/app/public/logos-muni
    ->visibility('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_muni')->label('Código')->sortable(),
                TextColumn::make('nombre')->label('Nombre')->searchable(),
                TextColumn::make('nit')->label('NIT'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMunicipalidades::route('/'),
            'create' => Pages\CreateMunicipalidad::route('/create'),
            'edit' => Pages\EditMunicipalidad::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
{
    // Solo el administrador tiene permiso para ver este recurso
    return auth()->user()?->rol === 'admin';
}
}