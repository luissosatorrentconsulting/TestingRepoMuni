<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Categoria;
use App\Models\Municipalidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.muni_id' => 1]);
    }

    public function test_pdf_and_fix_routes_require_login(): void
    {
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $activo = Activo::create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Laptop',
            'estado' => 'BUENO',
            'costo_original' => 1000,
            'fecha_compra' => now(),
            'valor_desecho' => 0,
        ]);

        $this->get("/reporte-historial/{$activo->id}")->assertRedirect('/login');
        $this->get('/fix-db-fase2')->assertRedirect('/login');
        $this->get('/fix-db-prod')->assertRedirect('/login');

        $this->get('/login')->assertRedirect('/admin/login');
    }

    public function test_break_glass_routes_require_the_maintenance_token(): void
    {
        // El valor real viene de phpunit.xml (MAINTENANCE_TOKEN=test-maintenance-token).
        $this->get('/crear-usuario')->assertForbidden();
        $this->get('/crear-usuario?token=incorrecto')->assertForbidden();
        $this->get('/crear-usuario?token=test-maintenance-token')->assertOk();
    }
}
