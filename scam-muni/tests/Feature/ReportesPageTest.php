<?php

namespace Tests\Feature;

use App\Filament\Pages\Reportes;
use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Municipalidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportesPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_reportes_page_shows_the_right_links_once_a_record_is_selected(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin']);
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

        $this->get('/admin/reportes')->assertOk();

        $component = Livewire::test(Reportes::class);

        // Antes de elegir nada, no hay reportes por empleado/activo/asignación.
        $component->assertSee('Elegí un empleado arriba');

        $component->set('data.empleado_id', $empleado->id);
        $this->assertEquals(
            ['empleado.resguardo.pdf', 'empleado.traslados.pdf'],
            array_column($component->instance()->getReportesEmpleado(), 'route'),
        );

        $component->set('data.activo_id', $activo->id);
        $this->assertEquals(
            ['activos.historial.pdf'],
            array_column($component->instance()->getReportesActivo(), 'route'),
        );

        $component->set('data.asignacion_id', $asignacion->id);
        $this->assertEquals(
            ['asignacion.acta.pdf'],
            array_column($component->instance()->getReportesAsignacion(), 'route'),
        );

        // Las rutas detrás de esos botones deben resolver de verdad.
        $this->get(route('empleado.resguardo.pdf', $empleado))->assertOk();
        $this->get(route('empleado.traslados.pdf', $empleado))->assertOk();
        $this->get(route('activos.historial.pdf', $activo))->assertOk();
        $this->get(route('asignacion.acta.pdf', $asignacion))->assertOk();
        $this->get(route('reportes.inventario-general.pdf'))->assertOk();
    }

    public function test_report_buttons_render_valid_javascript_not_a_raw_blade_directive(): void
    {
        // Regresión: @js(...) dentro de un atributo de <x-filament::button ...>
        // no se compilaba y quedaba como texto literal "@js(...)" en el HTML,
        // lo que rompía el click (JS inválido) sin dar ningún error visible.
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin2@test.com', 'password' => bcrypt('x'), 'rol' => 'admin']);
        $empleado = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '999', 'activo' => true]);

        $this->actingAs($user);

        $component = Livewire::test(Reportes::class);
        $component->set('data.empleado_id', $empleado->id);

        $html = $component->html();

        $this->assertStringNotContainsString('@js(', $html);
        $this->assertStringContainsString(
            "pdfUrl = '" . addcslashes(route('empleado.resguardo.pdf', $empleado), '/') . "'",
            $html,
        );
    }
}
