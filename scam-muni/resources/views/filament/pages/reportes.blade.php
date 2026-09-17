<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="grid gap-6 mt-6">

        <x-filament::section>
            <x-slot name="heading">Reportes por Empleado</x-slot>

            @php $reportes = $this->getReportesEmpleado(); @endphp

            @if (empty($reportes))
                <p class="text-sm text-gray-500 dark:text-gray-400">Elegí un empleado arriba para ver sus reportes disponibles.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach ($reportes as $reporte)
                        <x-filament::button
                            tag="a"
                            :href="route($reporte['route'], $reporte['params'])"
                            target="_blank"
                            icon="heroicon-o-document-arrow-down"
                            color="info"
                        >
                            {{ $reporte['label'] }}
                        </x-filament::button>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Reportes por Activo</x-slot>

            @php $reportes = $this->getReportesActivo(); @endphp

            @if (empty($reportes))
                <p class="text-sm text-gray-500 dark:text-gray-400">Elegí un activo arriba para ver sus reportes disponibles.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach ($reportes as $reporte)
                        <x-filament::button
                            tag="a"
                            :href="route($reporte['route'], $reporte['params'])"
                            target="_blank"
                            icon="heroicon-o-document-arrow-down"
                            color="info"
                        >
                            {{ $reporte['label'] }}
                        </x-filament::button>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Reportes por Asignación</x-slot>

            @php $reportes = $this->getReportesAsignacion(); @endphp

            @if (empty($reportes))
                <p class="text-sm text-gray-500 dark:text-gray-400">Elegí una asignación arriba para ver sus reportes disponibles.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach ($reportes as $reporte)
                        <x-filament::button
                            tag="a"
                            :href="route($reporte['route'], $reporte['params'])"
                            target="_blank"
                            icon="heroicon-o-document-arrow-down"
                            color="info"
                        >
                            {{ $reporte['label'] }}
                        </x-filament::button>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Reportes Generales</x-slot>

            <div class="flex flex-wrap gap-3">
                @foreach ($this->getReportesGenerales() as $reporte)
                    <x-filament::button
                        tag="a"
                        :href="route($reporte['route'], $reporte['params'])"
                        target="_blank"
                        icon="heroicon-o-document-arrow-down"
                        color="success"
                    >
                        {{ $reporte['label'] }}
                    </x-filament::button>
                @endforeach
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
