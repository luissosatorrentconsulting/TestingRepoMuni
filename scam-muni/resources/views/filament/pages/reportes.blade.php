<x-filament-panels::page>
    <div x-data="{ pdfUrl: null, pdfLabel: null }">

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
                                type="button"
                                icon="heroicon-o-eye"
                                color="info"
                                x-on:click="pdfUrl = {{ \Illuminate\Support\Js::from(route($reporte['route'], $reporte['params'])) }}; pdfLabel = {{ \Illuminate\Support\Js::from($reporte['label']) }}"
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
                                type="button"
                                icon="heroicon-o-eye"
                                color="info"
                                x-on:click="pdfUrl = {{ \Illuminate\Support\Js::from(route($reporte['route'], $reporte['params'])) }}; pdfLabel = {{ \Illuminate\Support\Js::from($reporte['label']) }}"
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
                                type="button"
                                icon="heroicon-o-eye"
                                color="info"
                                x-on:click="pdfUrl = {{ \Illuminate\Support\Js::from(route($reporte['route'], $reporte['params'])) }}; pdfLabel = {{ \Illuminate\Support\Js::from($reporte['label']) }}"
                            >
                                {{ $reporte['label'] }}
                            </x-filament::button>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Bajas del Período</x-slot>

                @php $reporte = $this->getReporteBajas(); @endphp
                <div class="flex flex-wrap gap-3">
                    <x-filament::button
                        type="button"
                        icon="heroicon-o-eye"
                        color="danger"
                        x-on:click="pdfUrl = {{ \Illuminate\Support\Js::from(route($reporte['route'], $reporte['params'])) }}; pdfLabel = {{ \Illuminate\Support\Js::from($reporte['label']) }}"
                    >
                        Ver Bajas del Período
                    </x-filament::button>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Reportes Generales</x-slot>

                <div class="flex flex-wrap gap-3">
                    @foreach ($this->getReportesGenerales() as $reporte)
                        <x-filament::button
                            type="button"
                            icon="heroicon-o-eye"
                            color="success"
                            x-on:click="pdfUrl = {{ \Illuminate\Support\Js::from(route($reporte['route'], $reporte['params'])) }}; pdfLabel = {{ \Illuminate\Support\Js::from($reporte['label']) }}"
                        >
                            {{ $reporte['label'] }}
                        </x-filament::button>
                    @endforeach
                </div>
            </x-filament::section>

        </div>

        {{-- Visor de reportes: en vez de abrir el PDF directo, se muestra acá dentro --}}
        <div
            x-show="pdfUrl"
            x-cloak
            style="position: fixed; inset: 0; z-index: 50;"
            class="flex items-center justify-center p-4"
        >
            <div
                x-on:click="pdfUrl = null"
                style="position: absolute; inset: 0; background: rgba(0,0,0,0.6);"
            ></div>

            <div
                class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full"
                style="max-width: 1100px;"
            >
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white" x-text="pdfLabel"></h3>

                    <div class="flex items-center gap-4">
                        <a
                            :href="pdfUrl"
                            target="_blank"
                            class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
                        >
                            Abrir en pestaña nueva ↗
                        </a>

                        <button
                            type="button"
                            x-on:click="pdfUrl = null"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <span class="sr-only">Cerrar</span>
                            ✕
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    <iframe
                        :src="pdfUrl"
                        style="width: 100%; height: 75vh; border: 0; border-radius: 0.5rem;"
                        class="bg-white"
                    ></iframe>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
