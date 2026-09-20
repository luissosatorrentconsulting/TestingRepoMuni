<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivoResource\Pages\EditActivo;
use App\Filament\Resources\ActivoResource\RelationManagers\AsignacionesRelationManager;
use App\Filament\Resources\AsignacionResource\Pages\EditAsignacion;
use App\Filament\Resources\AsignacionResource\Pages\ListAsignacions;
use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Municipalidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AsignacionUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    private function crearEscenario(): array
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $emp1 = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '111']);
        $emp2 = Empleado::create(['nombre_completo' => 'Maria Lopez', 'dpi' => '222']);
        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop Dell',
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
            'observaciones' => 'Entrega inicial',
        ]);

        return compact('user', 'categoria', 'emp1', 'emp2', 'activo', 'asignacion');
    }

    public function test_corregir_una_asignacion_no_permite_cambiar_el_responsable_ni_el_bien(): void
    {
        ['user' => $user, 'categoria' => $categoria, 'emp2' => $emp2, 'asignacion' => $asignacion] = $this->crearEscenario();

        $otroActivo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Impresora Suelta',
            'estado' => 'BUENO',
            'costo_original' => 300,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(EditAsignacion::class, ['record' => $asignacion->id])
            ->fillForm([
                'empleado_id' => $emp2->id, // intento de cambiar el responsable
                'activo_id' => $otroActivo->id, // intento de cambiar el bien
                'documento_respaldo' => 'ACTA-1-CORREGIDA',
                'observaciones' => 'Se corrigió el número de acta',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $asignacion->refresh();
        $this->assertEquals('ACTA-1-CORREGIDA', $asignacion->documento_respaldo, 'Sí debe poder corregir otros campos');
        $this->assertNotEquals($emp2->id, $asignacion->empleado_id, 'El responsable NO debe haber cambiado');
        $this->assertNotEquals($otroActivo->id, $asignacion->activo_id, 'El bien NO debe haber cambiado');
    }

    public function test_corregir_desde_el_detalle_del_bien_edita_el_acta_no_el_nombre_del_empleado(): void
    {
        ['user' => $user, 'emp1' => $emp1, 'activo' => $activo, 'asignacion' => $asignacion] = $this->crearEscenario();

        $this->actingAs($user);

        Livewire::test(AsignacionesRelationManager::class, [
            'ownerRecord' => $activo,
            'pageClass' => EditActivo::class,
        ])
            ->mountTableAction('edit', $asignacion)
            ->assertTableActionDataSet([
                'documento_respaldo' => 'ACTA-1',
                'observaciones' => 'Entrega inicial',
            ])
            ->setTableActionData([
                'documento_respaldo' => 'ACTA-1-B',
                'observaciones' => 'Corregido desde el detalle del bien',
                'fecha_asignacion' => $asignacion->fecha_asignacion->toDateString(),
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $asignacion->refresh();
        $this->assertEquals('ACTA-1-B', $asignacion->documento_respaldo);
        $this->assertEquals('Corregido desde el detalle del bien', $asignacion->observaciones);

        // Lo importante: el nombre del empleado NO debe haber cambiado
        // (antes este formulario estaba conectado a ese campo por error).
        $this->assertEquals('Juan Perez', $emp1->fresh()->nombre_completo);
    }

    public function test_se_puede_reasignar_desde_el_detalle_del_bien(): void
    {
        ['user' => $user, 'emp2' => $emp2, 'activo' => $activo, 'asignacion' => $asignacion] = $this->crearEscenario();

        $this->actingAs($user);

        Livewire::test(AsignacionesRelationManager::class, [
            'ownerRecord' => $activo,
            'pageClass' => EditActivo::class,
        ])
            ->callTableAction('reasignar', $asignacion->fresh(), data: [
                'empleado_id' => $emp2->id,
                'fecha_asignacion' => now()->toDateString(),
                'documento_respaldo' => 'ACTA-2',
                'observaciones' => 'Reasignación desde el detalle del bien',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertFalse($asignacion->fresh()->activa, 'La asignación anterior debe quedar cerrada');
        $nueva = Asignacion::where('activo_id', $activo->id)->where('activa', true)->first();
        $this->assertNotNull($nueva);
        $this->assertEquals($emp2->id, $nueva->empleado_id);
    }

    public function test_el_listado_de_asignaciones_se_puede_buscar_por_bien_o_por_responsable(): void
    {
        ['user' => $user, 'categoria' => $categoria, 'emp1' => $emp1, 'emp2' => $emp2, 'asignacion' => $asignacionJuan] = $this->crearEscenario();

        $otroActivo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Impresora HP',
            'estado' => 'BUENO',
            'costo_original' => 500,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        $asignacionMaria = Asignacion::create([
            'activo_id' => $otroActivo->id,
            'empleado_id' => $emp2->id,
            'fecha_asignacion' => now(),
            'documento_respaldo' => 'ACTA-3',
            'observaciones' => 'Entrega',
        ]);

        $this->actingAs($user);

        // Buscar por nombre del responsable.
        Livewire::test(ListAsignacions::class)
            ->searchTable('Juan Perez')
            ->assertCanSeeTableRecords([$asignacionJuan])
            ->assertCanNotSeeTableRecords([$asignacionMaria]);

        // Buscar por descripción del bien.
        Livewire::test(ListAsignacions::class)
            ->searchTable('Impresora')
            ->assertCanSeeTableRecords([$asignacionMaria])
            ->assertCanNotSeeTableRecords([$asignacionJuan]);
    }
}
