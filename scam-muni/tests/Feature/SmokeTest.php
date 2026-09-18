<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Asignacion;
use App\Models\Categoria;
use App\Models\Color;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Marca;
use App\Models\Municipalidad;
use App\Models\Oficina;
use App\Models\Proveedor;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_modified_pages_render_without_errors(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol' => 'admin',
            'municipalidad_id' => 1,
        ]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $marca = Marca::create(['nombre' => 'HP', 'municipalidad_id' => 1]);
        $color = Color::create(['nombre' => 'Negro', 'municipalidad_id' => 1]);
        $proveedor = Proveedor::create(['nombre' => 'Prov', 'municipalidad_id' => 1]);
        $departamento = Departamento::create(['nombre' => 'Recursos Humanos', 'municipalidad_id' => 1]);
        $oficina = Oficina::create(['nombre' => 'Oficina Central', 'departamento_id' => $departamento->id]);
        $puesto = Puesto::create(['nombre' => 'Encargado', 'oficina_id' => $oficina->id]);
        $empleado = Empleado::create(['nombre_completo' => 'Juan Perez', 'dpi' => '123', 'activo' => true, 'puesto_id' => $puesto->id]);

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

        Asignacion::create([
            'activo_id' => $activo->id,
            'empleado_id' => $empleado->id,
            'fecha_asignacion' => now(),
            'documento_respaldo' => 'ACTA-1',
            'observaciones' => 'Entrega',
        ]);

        $this->actingAs($user);

        $urls = [
            '/admin', // Dashboard: renderiza los widgets (ActivosPorCategoriaChart, etc.)
            '/admin/activos',
            '/admin/activos/create',
            "/admin/activos/{$activo->id}/edit",
            '/admin/bien-varios',
            '/admin/bien-varios/create',
            '/admin/asignacions',
            '/admin/asignacions/create',
            '/admin/empleados',
            '/admin/empleados/create',
            "/admin/empleados/{$empleado->id}/edit",
            '/admin/categorias',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $response->assertOk();
        }

        // PDF routes
        $this->get("/reporte-historial/{$activo->id}")->assertOk();
        $this->get("/empleado/{$empleado->id}/resguardo-pdf")->assertOk();
        $this->get("/empleado/{$empleado->id}/traslados-pdf")->assertOk();
    }
}
