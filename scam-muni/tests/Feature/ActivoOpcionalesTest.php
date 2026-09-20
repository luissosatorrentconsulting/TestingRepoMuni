<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivoResource\Pages\CreateActivo;
use App\Models\Activo;
use App\Models\Categoria;
use App\Models\Municipalidad;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivoOpcionalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_se_puede_crear_un_activo_sin_marca_ni_color(): void
    {
        config(['app.muni_id' => 1]);
        Municipalidad::create(['id' => 1, 'nombre' => 'Test', 'codigo_muni' => '910']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'rol' => 'admin', 'municipalidad_id' => 1]);
        $categoria = Categoria::create(['prefijo' => 'MOB', 'nombre' => 'Mobiliario', 'porcentaje_depreciacion' => 20]);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor Uno']);

        $this->actingAs($user);

        Livewire::test(CreateActivo::class)
            ->fillForm([
                'categoria_id' => $categoria->id,
                'descripcion' => 'Escritorio sin marca ni color',
                'marca_id' => null,
                'color_id' => null,
                'proveedor_id' => $proveedor->id,
                'costo_original' => 500,
                'fecha_compra' => now()->toDateString(),
                'valor_desecho' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $activo = Activo::where('descripcion', 'Escritorio sin marca ni color')->first();
        $this->assertNotNull($activo, 'El activo debió crearse aunque no se eligió marca ni color');
        $this->assertNull($activo->marca_id);
        $this->assertNull($activo->color_id);
    }
}
