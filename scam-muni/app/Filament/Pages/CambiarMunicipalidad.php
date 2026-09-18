<?php

namespace App\Filament\Pages;

use App\Models\Municipalidad;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CambiarMunicipalidad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Cambiar Municipalidad';
    protected static ?string $title = 'Cambiar Municipalidad';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.cambiar-municipalidad';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'municipalidad_id' => session('acting_as_municipalidad_id'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('municipalidad_id')
                    ->label('Municipalidad con la que querés trabajar')
                    ->options(fn () => Municipalidad::pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Todo lo que veas y crees en el panel (Activos, Empleados, catálogos, etc.) va a pertenecer a esta municipalidad hasta que la cambies.'),
            ])
            ->statePath('data');
    }

    public function guardar(): void
    {
        $data = $this->form->getState();

        session(['acting_as_municipalidad_id' => $data['municipalidad_id']]);

        Notification::make()
            ->title('Municipalidad activa actualizada')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function municipalidadActual(): ?Municipalidad
    {
        $id = session('acting_as_municipalidad_id');

        return $id ? Municipalidad::find($id) : null;
    }
}
