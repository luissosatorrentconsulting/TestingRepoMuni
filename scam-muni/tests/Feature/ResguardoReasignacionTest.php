<?php

namespace Tests\Feature;

use App\Filament\Resources\AsignacionResource\Pages\ListAsignacions;
use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Municipalidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ResguardoReasignacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_al_reasignar_el_bien_desaparece_del_resguardo_del_responsable_anterior(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $andre = Empleado::create(['nombre_completo' => 'Andre Gomez', 'dpi' => '111']);
        $marco = Empleado::create(['nombre_completo' => 'Marco Diaz', 'dpi' => '222']);

        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop Compartida',
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $asignacionAndre = Asignacion::create([
            'activo_id' => $activo->id,
            'empleado_id' => $andre->id,
            'fecha_asignacion' => now()->subDays(30),
            'documento_respaldo' => 'ACTA-1',
            'observaciones' => 'Entrega a Andre',
        ]);

        $this->actingAs($user);

        // Antes de reasignar: el activo SÍ debe aparecer en el resguardo de Andre.
        $pdfAntes = $this->get(route('empleado.resguardo.pdf', $andre));
        $pdfAntes->assertOk();

        // Reasignar de Andre a Marco (mismo flujo real: crea una asignación
        // nueva, la del observer cierra la anterior).
        Livewire::test(ListAsignacions::class)
            ->callTableAction('reasignar', $asignacionAndre->fresh(), data: [
                'empleado_id' => $marco->id,
                'fecha_asignacion' => now()->toDateString(),
                'documento_respaldo' => 'ACTA-2',
                'observaciones' => 'Traslado de Andre a Marco',
            ])
            ->assertHasNoTableActionErrors();

        // Verificación a nivel de datos: la consulta real que usa el reporte.
        $activosDeAndre = Activo::whereHas('asignacionActiva', fn ($q) => $q->where('empleado_id', $andre->id))
            ->where('es_baja', false)
            ->get();
        $this->assertCount(0, $activosDeAndre, 'El bien ya NO debe aparecer como responsabilidad de Andre');

        $activosDeMarco = Activo::whereHas('asignacionActiva', fn ($q) => $q->where('empleado_id', $marco->id))
            ->where('es_baja', false)
            ->get();
        $this->assertCount(1, $activosDeMarco, 'El bien debe aparecer como responsabilidad de Marco');

        // Verificación end-to-end: el PDF de Andre ya no lo debería traer, el
        // de Marco sí. Comparamos el tamaño del PDF de Andre antes/después:
        // con el activo ya no listado, el PDF de Andre encoge.
        $pdfDespuesAndre = $this->get(route('empleado.resguardo.pdf', $andre));
        $pdfDespuesAndre->assertOk();
        $this->assertLessThan(
            strlen($pdfAntes->getContent()),
            strlen($pdfDespuesAndre->getContent()),
            'El PDF de Andre debe encoger (una fila menos) al ya no tener el bien'
        );

        $pdfMarco = $this->get(route('empleado.resguardo.pdf', $marco));
        $pdfMarco->assertOk();
    }

    public function test_el_reporte_de_traslados_solo_suma_lo_que_el_empleado_todavia_tiene(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $andre = Empleado::create(['nombre_completo' => 'Andre Gomez', 'dpi' => '111']);
        $marco = Empleado::create(['nombre_completo' => 'Marco Diaz', 'dpi' => '222']);

        // Activo A: Andre lo recibe y se queda con él (vigente).
        $activoA = Activo::create([
            'categoria_id' => $categoria->id, 'descripcion' => 'Silla A',
            'estado' => 'BUENO', 'costo_original' => 300, 'fecha_compra' => now(), 'valor_desecho' => 0,
        ]);
        $asigA = Asignacion::create([
            'activo_id' => $activoA->id, 'empleado_id' => $andre->id,
            'fecha_asignacion' => now(), 'documento_respaldo' => 'ACTA-A', 'observaciones' => 'Entrega',
        ]);

        // Activo B: Andre lo recibe pero después se lo trasladan a Marco (ya no es suyo).
        $activoB = Activo::create([
            'categoria_id' => $categoria->id, 'descripcion' => 'Laptop B',
            'estado' => 'BUENO', 'costo_original' => 1000, 'fecha_compra' => now(), 'valor_desecho' => 0,
        ]);
        $asigB = Asignacion::create([
            'activo_id' => $activoB->id, 'empleado_id' => $andre->id,
            'fecha_asignacion' => now()->subDays(10), 'documento_respaldo' => 'ACTA-B1', 'observaciones' => 'Entrega',
        ]);

        $this->actingAs($user);

        Livewire::test(ListAsignacions::class)
            ->callTableAction('reasignar', $asigB->fresh(), data: [
                'empleado_id' => $marco->id,
                'fecha_asignacion' => now()->toDateString(),
                'documento_respaldo' => 'ACTA-B2',
                'observaciones' => 'Traslado a Marco',
            ])
            ->assertHasNoTableActionErrors();

        // Replicamos exactamente el cálculo que hace la vista del reporte.
        $traslados = \App\Models\MovimientoActivo::where('entregado_por_id', $andre->id)
            ->orWhere('recibido_por_id', $andre->id)
            ->with(['activo.categoria', 'activo.asignacionActiva'])
            ->get();

        // Andre aparece en 3 movimientos (recibió A, recibió B, entregó B),
        // pero el total debe ser solo el costo de A (lo único que aún tiene).
        $this->assertCount(3, $traslados, 'Andre debe seguir viendo su historial completo');

        $total = 0;
        foreach ($traslados as $t) {
            if ($t->activo->asignacionActiva?->empleado_id === $andre->id) {
                $total += $t->activo->costo_original;
            }
        }

        $this->assertEquals(300.0, (float) $total, 'Solo debe sumar la Silla A, no la Laptop B que ya no es suya');

        $this->get(route('empleado.traslados.pdf', $andre))->assertOk();
    }
}
