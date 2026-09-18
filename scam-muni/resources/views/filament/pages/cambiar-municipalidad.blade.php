<x-filament-panels::page>
    @php $actual = $this->municipalidadActual(); @endphp

    <x-filament::section>
        <x-slot name="heading">Municipalidad activa</x-slot>

        @if($actual)
            <p class="text-sm">
                Estás trabajando actualmente con <strong>{{ $actual->nombre }}</strong> ({{ $actual->codigo_muni }}).
            </p>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Todavía no elegiste ninguna municipalidad. Mientras tanto, las pantallas de Activos, Empleados y
                catálogos van a aparecer vacías.
            </p>
        @endif
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Elegir municipalidad</x-slot>

        <form wire:submit="guardar">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit">
                    Cambiar
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
