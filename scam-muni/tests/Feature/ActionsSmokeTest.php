<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivoResource\Pages\ListActivos;
use App\Filament\Resources\AsignacionResource\Pages\ListAsignacions;
use App\Filament\Resources\EmpleadoResource\Pages\ListEmpleados;
use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Categoria;
use App\Models\Color;
use App\Models\Empleado;
use App\Models\Marca;
use App\Models\Municipalidad;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActionsSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_reasignar_and_baja_actions_work(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $marca = Marca::create(['nombre' => 'HP', 'municipalidad_id' => 1]);
        $color = Color::create(['nombre' => 'Negro', 'municipalidad_id' => 1]);
        $proveedor = Proveedor::create(['nombre' => 'Prov', 'municipalidad_id' => 1]);
        $emp1 = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '123', 'activo' => true]);
        $emp2 = Empleado::create(['nombre_completo' => 'Maria Lopez', 'dpi' => '456', 'activo' => true]);

        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop',
            'marca_id' => $marca->id,
            'color_id' => $color->id,
            'proveedor_id' => $proveedor->id,
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $asignacion = Asignacion::create([
            'activo_id' => $activo->id,
            'empleado_id' => $emp1->id,
            'fecha_asignacion' => now(),
            'documento_respaldo' => 'ACTA-1',
            'observaciones' => 'Entrega',
        ]);

        $this->actingAs($user);

        Livewire::test(ListAsignacions::class)
            ->callTableAction('reasignar', $asignacion->fresh(), data: [
                'empleado_id' => $emp2->id,
                'fecha_asignacion' => now()->toDateString(),
                'documento_respaldo' => 'ACTA-2',
                'observaciones' => 'Reasignacion de prueba',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertFalse($asignacion->fresh()->activa);
        $this->assertEquals(2, Asignacion::where('activo_id', $activo->id)->count());
        $this->assertEquals(2, \App\Models\MovimientoActivo::where('activo_id', $activo->id)->count());

        Livewire::test(ListActivos::class)
            ->callTableAction('baja', $activo, data: [
                'fecha_baja' => now()->toDateString(),
                'motivo_baja' => 'Ya no sirve',
            ])
            ->assertHasNoTableActionErrors();

        $activo->refresh();
        $this->assertTrue($activo->es_baja);
        $this->assertNotNull($activo->fecha_baja);
        $this->assertNull($activo->acta_baja, 'El No. de Acta es opcional: no pasarlo no debe dar error');
        $this->assertEquals('Ya no sirve', $activo->motivo_baja);
    }

    public function test_dar_de_baja_guarda_el_acta_cuando_se_llena_y_se_limpia_al_reactivar(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Impresora',
            'estado' => 'BUENO',
            'costo_original' => 800,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(ListActivos::class)
            ->callTableAction('baja', $activo, data: [
                'fecha_baja' => now()->toDateString(),
                'acta_baja' => 'ACTA-BAJA-001',
                'motivo_baja' => 'Ya no enciende',
            ])
            ->assertHasNoTableActionErrors();

        $activo->refresh();
        $this->assertEquals('ACTA-BAJA-001', $activo->acta_baja);

        // El listado de Activos oculta los dados de baja por defecto (filtro
        // "Activos Disponibles"), así que hay que pasar al filtro "Ver solo
        // Bajas" para que la fila exista en la tabla y se pueda reactivar
        // — igual que tendría que hacerlo un usuario real.
        Livewire::test(ListActivos::class)
            ->filterTable('es_baja', true)
            ->callTableAction('reactivar', $activo)
            ->assertHasNoTableActionErrors();

        $activo->refresh();
        $this->assertFalse($activo->es_baja);
        $this->assertNull($activo->acta_baja, 'Al reactivar debe limpiarse el acta de baja anterior');
    }

    public function test_pdf_viewer_modals_render_without_errors(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $empleado = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '123', 'activo' => true]);
        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop',
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        $asignacion = Asignacion::create([
            'activo_id' => $activo->id,
            'empleado_id' => $empleado->id,
            'fecha_asignacion' => now(),
            'documento_respaldo' => 'ACTA-1',
            'observaciones' => 'Entrega',
        ]);

        $this->actingAs($user);

        Livewire::test(ListActivos::class)
            ->mountTableAction('historial', $activo)
            ->assertHasNoTableActionErrors()
            ->assertSee(route('activos.historial.pdf', $activo), escape: false);

        Livewire::test(ListEmpleados::class)
            ->mountTableAction('imprimirResguardo', $empleado)
            ->assertHasNoTableActionErrors()
            ->assertSee(route('empleado.resguardo.pdf', $empleado), escape: false);

        Livewire::test(ListEmpleados::class)
            ->mountTableAction('imprimirTraslados', $empleado)
            ->assertHasNoTableActionErrors()
            ->assertSee(route('empleado.traslados.pdf', $empleado), escape: false);

        Livewire::test(ListAsignacions::class)
            ->mountTableAction('pdf', $asignacion)
            ->assertHasNoTableActionErrors()
            ->assertSee(route('asignacion.acta.pdf', $asignacion), escape: false);
    }
}
