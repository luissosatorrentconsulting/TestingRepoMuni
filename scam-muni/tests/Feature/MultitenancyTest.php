<?php

namespace Tests\Feature;

use App\Filament\Pages\CambiarMunicipalidad;
use App\Models\Activo;
use App\Models\Categoria;
use App\Models\Color;
use App\Models\Empleado;
use App\Models\GestionAutoridad;
use App\Models\Marca;
use App\Models\Municipalidad;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class MultitenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_backfill_no_asume_que_la_municipalidad_existente_es_id_1(): void
    {
        // Simula una producción donde la municipalidad no quedó con id=1
        // (por ejemplo, porque se creó vía MUNICIPALIDAD_DEFAULT_ID=5 en algún
        // momento). El backfill de la migración debe detectar el id real, no
        // asumir 1 a ciegas.
        // 'id' no está en el fillable de Municipalidad, así que se inserta
        // directo para forzar el escenario ("id" != 1).
        DB::table('municipalidades')->insert([
            'id' => 7, 'nombre' => 'Municipalidad Real', 'codigo_muni' => '555',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Simulamos "data vieja" que quedó sin municipalidad_id (como estaría
        // justo antes de correr esta migración en un servidor real).
        DB::table('categorias')->insert([
            'prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20, 'municipalidad_id' => null,
        ]);
        DB::table('users')->insert([
            'name' => 'Admin Original', 'email' => 'original@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => null,
        ]);

        // Reprocesamos el backfill tal como correría en el servidor real.
        $migration = require database_path('migrations/2026_09_18_000000_add_multitenancy_support.php');
        $migration->up();

        $this->assertEquals(7, DB::table('categorias')->where('prefijo', 'MOB')->value('municipalidad_id'));
        $this->assertEquals(7, DB::table('users')->where('email', 'original@test.com')->value('municipalidad_id'));
    }

    public function test_dos_municipalidades_no_se_ven_los_datos_entre_si(): void
    {
        $muniA = Municipalidad::create(['nombre' => 'Municipalidad A', 'codigo_muni' => '100']);
        $muniB = Municipalidad::create(['nombre' => 'Municipalidad B', 'codigo_muni' => '200']);

        $adminA = User::create(['name' => 'Admin A', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => $muniA->id]);
        $adminB = User::create(['name' => 'Admin B', 'email' => 'b@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => $muniB->id]);

        // --- Municipalidad A crea sus propios datos ---
        $this->actingAs($adminA);
        config(['app.muni_id' => $muniA->id]);

        $catA = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $marcaA = Marca::create(['nombre' => 'HP']);
        $colorA = Color::create(['nombre' => 'Negro']);
        $proveedorA = Proveedor::create(['nombre' => 'Proveedor A']);
        $empleadoA = Empleado::create(['nombre_completo' => 'Empleado A', 'dpi' => 'A1']);
        $activoA = Activo::create([
            'categoria_id' => $catA->id,
            'descripcion' => 'Laptop A',
            'marca_id' => $marcaA->id,
            'color_id' => $colorA->id,
            'proveedor_id' => $proveedorA->id,
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        GestionAutoridad::create([
            'empleado_id' => $empleadoA->id,
            'cargo' => 'ALCALDE',
            'fecha_inicio' => now()->subYear(),
            'periodo_gestion' => '2024-2028',
        ]);

        // --- Municipalidad B crea los suyos, con la MISMA categoría "MOB" ---
        $this->actingAs($adminB);
        config(['app.muni_id' => $muniB->id]);

        $catB = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $marcaB = Marca::create(['nombre' => 'Dell']);
        $empleadoB = Empleado::create(['nombre_completo' => 'Empleado B', 'dpi' => 'B1']);
        $activoB = Activo::create([
            'categoria_id' => $catB->id,
            'descripcion' => 'Laptop B',
            'marca_id' => $marcaB->id,
            'estado' => 'BUENO',
            'costo_original' => 2000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        GestionAutoridad::create([
            'empleado_id' => $empleadoB->id,
            'cargo' => 'ALCALDE',
            'fecha_inicio' => now()->subYear(),
            'periodo_gestion' => '2024-2028',
        ]);

        // === Verificaciones desde el punto de vista de A ===
        $this->actingAs($adminA);
        config(['app.muni_id' => $muniA->id]);

        $this->assertEquals(1, Categoria::count(), 'A no debe ver la categoría MOB de B');
        $this->assertEquals(1, Marca::count());
        $this->assertEquals(1, Activo::count());
        $this->assertEquals('Laptop A', Activo::first()->descripcion);
        $this->assertNull(Activo::find($activoB->id), 'A no debe poder acceder al activo de B ni por ID directo');
        $this->assertEquals('Empleado A', $muniA->fresh()->getAutoridadActual('ALCALDE'), 'El alcalde de A no debe mezclarse con el de B');

        // === Verificaciones desde el punto de vista de B ===
        $this->actingAs($adminB);
        config(['app.muni_id' => $muniB->id]);

        $this->assertEquals(1, Categoria::count(), 'B no debe ver la categoría MOB de A');
        $this->assertEquals(1, Marca::count());
        $this->assertEquals(1, Activo::count());
        $this->assertEquals('Laptop B', Activo::first()->descripcion);
        $this->assertNull(Activo::find($activoA->id), 'B no debe poder acceder al activo de A ni por ID directo');
        $this->assertEquals('Empleado B', $muniB->fresh()->getAutoridadActual('ALCALDE'), 'El alcalde de B no debe mezclarse con el de A');

        // Cada municipalidad tiene su propio correlativo de códigos, aunque
        // compartan el mismo prefijo "MOB" — no deberían colisionar.
        $this->assertEquals('100-MOB-0001', $activoA->codigo_etiqueta);
        $this->assertEquals('200-MOB-0001', $activoB->codigo_etiqueta);
    }

    public function test_super_admin_sin_elegir_municipalidad_no_ve_nada_y_al_elegir_ve_lo_correcto(): void
    {
        $muniA = Municipalidad::create(['nombre' => 'Municipalidad A', 'codigo_muni' => '100']);
        $superAdmin = User::create(['name' => 'Super', 'email' => 'super@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => null]);
        $adminA = User::create(['name' => 'Admin A', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => $muniA->id]);

        $this->actingAs($adminA);
        config(['app.muni_id' => $muniA->id]);
        $catA = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        Activo::create([
            'categoria_id' => $catA->id,
            'descripcion' => 'Laptop A',
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($adminA->isSuperAdmin());

        // El Super Admin, sin haber elegido municipalidad, no debe ver nada.
        $this->actingAs($superAdmin);
        config(['app.muni_id' => null]);
        $this->assertEquals(0, Activo::count());
        $this->assertEquals(0, Categoria::count());

        // Ahora elige Municipalidad A desde el switcher.
        Livewire::test(CambiarMunicipalidad::class)
            ->set('data.municipalidad_id', $muniA->id)
            ->call('guardar');

        $this->assertEquals($muniA->id, session('acting_as_municipalidad_id'));

        // Con la sesión ya marcada, una request nueva debe traer los datos de A.
        config(['app.muni_id' => $muniA->id]); // lo que haría el middleware en la próxima request real
        $this->assertEquals(1, Activo::count());
        $this->assertEquals('Laptop A', Activo::first()->descripcion);
    }

    public function test_el_middleware_real_resuelve_la_municipalidad_por_request_via_http(): void
    {
        // A diferencia de los tests anteriores (que fijan config('app.muni_id')
        // a mano), esto pasa por el pipeline real de middleware registrado en
        // bootstrap/app.php, incluyendo el orden relativo a SubstituteBindings.
        $muniA = Municipalidad::create(['nombre' => 'Municipalidad A', 'codigo_muni' => '100']);
        $muniB = Municipalidad::create(['nombre' => 'Municipalidad B', 'codigo_muni' => '200']);
        $adminA = User::create(['name' => 'Admin A', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => $muniA->id]);
        $adminB = User::create(['name' => 'Admin B', 'email' => 'b@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => $muniB->id]);

        $categoriaA = null;
        $activoA = null;
        $this->actingAs($adminA);
        $this->get('/admin/activos/create'); // dispara el middleware real y fija config('app.muni_id') = A
        $categoriaA = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $activoA = Activo::create([
            'categoria_id' => $categoriaA->id,
            'descripcion' => 'Laptop A',
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);
        $this->assertEquals($muniA->id, $activoA->municipalidad_id);

        // B, en una request HTTP real, no debe poder acceder al PDF del
        // activo de A ni por URL directa (route model binding ya scopeado).
        // Se limpia la sesión antes de cada cambio de usuario de prueba:
        // AuthenticateSession compara el hash de password guardado en sesión
        // contra el del usuario actual, y detecta como inválida una sesión
        // "cambiada de usuario" a mitad de un test (algo que nunca pasa en
        // producción, donde cada usuario tiene su propia sesión real).
        $this->app['session']->flush();
        $this->actingAs($adminB);
        $this->get(route('activos.historial.pdf', $activoA))->assertNotFound();

        // Pero el admin de A sí puede.
        $this->app['session']->flush();
        $this->actingAs($adminA);
        $this->get(route('activos.historial.pdf', $activoA))->assertOk();

        // Y la lista de Activos del panel, vista por B, no debe traer el de A.
        $this->app['session']->flush();
        $this->actingAs($adminB);
        $this->get('/admin/activos')->assertOk()->assertDontSee('Laptop A');
    }
}
