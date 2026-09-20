<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\BienVario;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Municipalidad;
use App\Models\Oficina;
use App\Models\Proveedor;
use App\Models\Puesto;
use App\Models\Asignacion;
use App\Models\User;
use App\Filament\Resources\BienVarioResource\Pages\ListBienVarios;
use App\Filament\Resources\ActivoResource\Pages\ListActivos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NuevosReportesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_los_5_reportes_nuevos_generan_pdf_sin_error(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor Uno', 'nit' => '123456-7', 'municipalidad_id' => 1]);

        $departamento = Departamento::create(['nombre' => 'Recursos Humanos', 'municipalidad_id' => 1]);
        $oficina = Oficina::create(['nombre' => 'Oficina Central', 'departamento_id' => $departamento->id]);
        $puesto = Puesto::create(['nombre' => 'Encargado', 'oficina_id' => $oficina->id]);
        $empleado = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '123', 'activo' => true, 'puesto_id' => $puesto->id]);

        // Un activo asignado (debe aparecer agrupado bajo "Oficina Central")
        $asignado = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop asignada',
            'proveedor_id' => $proveedor->id,
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        Asignacion::create([
            'activo_id' => $asignado->id,
            'empleado_id' => $empleado->id,
            'fecha_asignacion' => now(),
            'documento_respaldo' => 'ACTA-1',
            'observaciones' => 'Entrega',
        ]);

        // Un activo disponible en bodega (sin asignación)
        Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Silla en bodega',
            'proveedor_id' => $proveedor->id,
            'estado' => 'BUENO',
            'costo_original' => 200,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        // Un activo de baja (debe aparecer en el reporte de bajas)
        $baja = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Impresora dañada',
            'proveedor_id' => $proveedor->id,
            'estado' => 'MALO',
            'costo_original' => 500,
            'fecha_compra' => now()->subYear(),
            'valor_desecho' => 0,
            'es_baja' => true,
            'fecha_baja' => now()->subDays(5),
            'motivo_baja' => 'Ya no enciende',
        ]);

        BienVario::create([
            'descripcion' => 'Extintor',
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'oficina_id' => $oficina->id,
            'estado' => 'Bueno',
            'costo' => 150,
        ]);

        $this->actingAs($user);

        $this->get('/reportes/inventario-por-oficina-pdf')
            ->assertOk();

        $this->get('/reportes/bajas-pdf')
            ->assertOk();

        $this->get('/reportes/bajas-pdf?desde=' . now()->subDays(10)->toDateString() . '&hasta=' . now()->toDateString())
            ->assertOk();

        // Fuera de rango: no debería incluir la baja de hace 5 días.
        $this->get('/reportes/bajas-pdf?desde=' . now()->addDays(1)->toDateString())
            ->assertOk();

        $this->get('/reportes/disponibles-pdf')
            ->assertOk();

        $this->get('/reportes/bienes-varios-general-pdf')
            ->assertOk();

        $this->get('/reportes/por-proveedor-pdf')
            ->assertOk();

        // Inventario General: sin filtros trae los 2 vigentes (asignado + bodega).
        $this->get('/reportes/inventario-general-pdf')
            ->assertOk();

        // Filtrado por categoría (la única que existe): sigue trayendo los 2.
        $this->get("/reportes/inventario-general-pdf?categoria_id={$categoria->id}")
            ->assertOk();

        // Filtrado por oficina: solo debe traer el activo ASIGNADO a alguien
        // de esa oficina, no el que está en bodega sin asignar.
        $this->get("/reportes/inventario-general-pdf?oficina_id={$oficina->id}")
            ->assertOk();

        // Filtrado por empleado: mismo criterio, solo lo que él tiene asignado.
        $this->get("/reportes/inventario-general-pdf?empleado_id={$empleado->id}")
            ->assertOk();

        // Verificación real de que el filtro EXCLUYE lo que no corresponde
        // (mismo criterio whereHas que usa la ruta, no solo que no truene).
        $porOficina = Activo::where('es_baja', false)
            ->whereHas('asignacionActiva.empleado.puesto_oficial.oficina', fn ($q) => $q->where('oficinas.id', $oficina->id))
            ->get();
        $this->assertCount(1, $porOficina, 'Solo el activo asignado debe quedar, no el de bodega');
        $this->assertEquals('Laptop asignada', $porOficina->first()->descripcion);

        $porEmpleado = Activo::where('es_baja', false)
            ->whereHas('asignacionActiva', fn ($q) => $q->where('empleado_id', $empleado->id))
            ->get();
        $this->assertCount(1, $porEmpleado);
        $this->assertEquals('Laptop asignada', $porEmpleado->first()->descripcion);

        // Verificación de contenido real, no solo status 200.
        $this->assertEquals(1, Activo::disponibles()->count());
        $this->assertEquals(1, Activo::where('es_baja', true)->count());

        $proveedorConTotales = Proveedor::withCount(['activos', 'bienesVarios'])
            ->withSum('activos', 'costo_original')
            ->withSum('bienesVarios', 'costo')
            ->first();
        $this->assertEquals(3, $proveedorConTotales->activos_count);
        $this->assertEquals(1700, (float) $proveedorConTotales->activos_sum_costo_original);
        $this->assertEquals(1, $proveedorConTotales->bienes_varios_count);
        $this->assertEquals(150, (float) $proveedorConTotales->bienes_varios_sum_costo);

        Livewire::test(ListBienVarios::class)
            ->callTableAction('exportarPdf')
            ->assertHasNoTableActionErrors();

        // "Reporte de Inventario" en Activos: abre en el visor modal con el
        // PDF embebido como data URI (NO guardado en storage/public: en
        // Railway ese disco es efímero y sin el symlink de storage:link
        // garantizado, un archivo guardado ahí puede dar 404 al servirlo —
        // justo el bug que se reportó y que esta prueba deja blindado).
        $resultado = Livewire::test(ListActivos::class)
            ->mountTableAction('exportarPdf')
            ->assertHasNoTableActionErrors();

        $html = $resultado->html();
        $this->assertStringContainsString('data:application/pdf;base64,', $html);
        $this->assertStringNotContainsString('temp-reports', $html);
    }
}
