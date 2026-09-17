<?php

namespace App\Filament\Pages;

use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class Reportes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Central de Reportes';
    protected static ?string $title = 'Central de Reportes';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.reportes';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('empleado_id')
                    ->label('Empleado')
                    ->placeholder('Buscar empleado por nombre...')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Empleado::query()
                        ->where('nombre_completo', 'like', "%{$search}%")
                        ->limit(20)
                        ->pluck('nombre_completo', 'id'))
                    ->getOptionLabelUsing(fn ($value) => Empleado::find($value)?->nombre_completo)
                    ->live(),

                Forms\Components\Select::make('activo_id')
                    ->label('Activo')
                    ->placeholder('Buscar por descripción o código...')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Activo::query()
                        ->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('codigo_etiqueta', 'like', "%{$search}%")
                        ->limit(20)
                        ->get()
                        ->mapWithKeys(fn (Activo $activo) => [$activo->id => "{$activo->codigo_etiqueta} — {$activo->descripcion}"]))
                    ->getOptionLabelUsing(function ($value) {
                        $activo = Activo::find($value);

                        return $activo ? "{$activo->codigo_etiqueta} — {$activo->descripcion}" : null;
                    })
                    ->live(),

                Forms\Components\Select::make('asignacion_id')
                    ->label('Asignación')
                    ->placeholder('Elegí una asignación...')
                    ->searchable()
                    ->options(fn () => Asignacion::query()
                        ->with(['activo', 'empleado'])
                        ->latest('fecha_asignacion')
                        ->limit(200)
                        ->get()
                        ->mapWithKeys(fn (Asignacion $asignacion) => [
                            $asignacion->id => sprintf(
                                '%s → %s (%s)',
                                $asignacion->activo?->descripcion ?? 'Activo eliminado',
                                $asignacion->empleado?->nombre_completo ?? 'Empleado eliminado',
                                optional($asignacion->fecha_asignacion)->format('d/m/Y'),
                            ),
                        ]))
                    ->live(),
            ])
            ->statePath('data')
            ->columns(1);
    }

    /**
     * @return array<int, array{label: string, route: string, params: mixed}>
     */
    public function getReportesEmpleado(): array
    {
        $id = $this->data['empleado_id'] ?? null;

        if (!$id) {
            return [];
        }

        return [
            ['label' => 'Resguardo (Tarjeta de Responsabilidad)', 'route' => 'empleado.resguardo.pdf', 'params' => $id],
            ['label' => 'Historial de Traslados', 'route' => 'empleado.traslados.pdf', 'params' => $id],
        ];
    }

    /**
     * @return array<int, array{label: string, route: string, params: mixed}>
     */
    public function getReportesActivo(): array
    {
        $id = $this->data['activo_id'] ?? null;

        if (!$id) {
            return [];
        }

        return [
            ['label' => 'Historial del Activo', 'route' => 'activos.historial.pdf', 'params' => $id],
        ];
    }

    /**
     * @return array<int, array{label: string, route: string, params: mixed}>
     */
    public function getReportesAsignacion(): array
    {
        $id = $this->data['asignacion_id'] ?? null;

        if (!$id) {
            return [];
        }

        return [
            ['label' => 'Acta de Asignación', 'route' => 'asignacion.acta.pdf', 'params' => $id],
        ];
    }

    /**
     * Reportes que no dependen de elegir un registro puntual.
     *
     * @return array<int, array{label: string, route: string, params: mixed}>
     */
    public function getReportesGenerales(): array
    {
        return [
            ['label' => 'Reporte General de Inventario', 'route' => 'reportes.inventario-general.pdf', 'params' => []],
        ];
    }
}
