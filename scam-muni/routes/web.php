<?php

use App\Models\User; 
use App\Models\Activo;
use App\Models\Municipalidad;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Barryvdh\DomPDF\Facade\Pdf;

// --- VISTAS / HOME ---
Route::get('/', function () {
    return redirect('/admin');
});

// --- REPORTES PDF ---

// Reporte de Historial de Activo
Route::get('/reporte-historial/{activo}', function (Activo $activo) {
    $muni = Municipalidad::find(config('app.muni_id', 1));
    $activo->load(['historial.entregador', 'historial.receptor', 'asignaciones.empleado']);
    
    $responsableActual = $activo->historial->last()?->receptor?->nombre_completo ?? 'SIN ASIGNAR';

    $pdf = Pdf::loadView('pdf.historial_activo', [
        'activo' => $activo,
        'historial' => $activo->historial,
        'responsable' => $responsableActual,
        'muni' => $muni,
    ]);

    return $pdf->stream("Historial-{$activo->codigo_etiqueta}.pdf");
})->name('activos.historial.pdf');

// Reporte de Resguardo de Empleado (Tarjeta de Responsabilidad)
Route::get('/empleado/{empleado}/resguardo-pdf', function (Empleado $empleado) {
    $muni = Municipalidad::find(config('app.muni_id', 1));
    $empleado->load(['puesto_oficial.departamento']);

    $activos = Activo::whereHas('asignaciones', function ($query) use ($empleado) {
        $query->where('empleado_id', $empleado->id);
    })->where('es_baja', false)->get();

    $pdf = Pdf::loadView('pdf.resguardo_empleado', [ 
        'empleado' => $empleado,
        'muni' => $muni,
        'activos' => $activos
    ]);

    return $pdf->stream("Resguardo-{$empleado->nombre_completo}.pdf");
})->name('empleado.resguardo.pdf');

// Reporte de Traslados
Route::get('/empleado/{empleado}/traslados-pdf', function (Empleado $empleado) {
    $muni = Municipalidad::find(config('app.muni_id', 1));
    $empleado->load(['puesto_oficial.departamento']);

    $traslados = \App\Models\MovimientoActivo::where('entregado_por_id', $empleado->id)
        ->with(['activo.categoria', 'receptor'])
        ->orderBy('fecha_movimiento', 'desc')
        ->get();

    $pdf = Pdf::loadView('pdf.traslados_empleado', [
        'empleado' => $empleado,
        'muni' => $muni,
        'traslados' => $traslados
    ])->setPaper('letter', 'landscape');

    return $pdf->stream("Traslados-{$empleado->nombre_completo}.pdf");
})->name('empleado.traslados.pdf');


// --- RUTAS DE MANTENIMIENTO Y UTILERÍAS ---

Route::get('/crear-usuario', function () {
    $user = User::updateOrCreate(
        ['email' => 'admin@muni.com'],
        [
            'name' => 'Admin Municipal',
            'password' => Hash::make('Muni2026*'),
        ]
    );
    return "Usuario creado o actualizado con éxito. Ya puedes ir a /admin";
});

Route::get('/migrar-todo', function() {
    try {
        Artisan::call('migrate --force');
        return "Tablas creadas con éxito: " . Artisan::output();
    } catch (\Exception $e) {
        return "Error al migrar: " . $e->getMessage();
    }
});

Route::get('/limpiar-y-migrar', function() {
    try {
        Artisan::call('migrate:fresh --force');
        return "Base de datos reconstruida con éxito. Por favor, vuelve a ejecutar la ruta /crear-usuario porque al borrar las tablas se borró el admin.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/reparar-todo', function() {
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'railway');
        $colName = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            Schema::dropIfExists($table->$colName);
        }

        Artisan::call('migrate --force');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        return "✅ ¡LIMPIEZA TOTAL! Tablas creadas. Ahora ve a /crear-usuario para poder entrar.";
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
});

// UNIFICADO: Fix de base de datos estructural + roles
Route::get('/fix-db-prod', function () {
    $reporte = [];

    if (!Schema::hasTable('marcas')) {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(1);
            $table->timestamps();
        });
        $reporte[] = "✅ Tabla 'marcas' creada.";
    }

    if (!Schema::hasTable('colores')) {
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(1);
            $table->timestamps();
        });
        $reporte[] = "✅ Tabla 'colores' creada.";
    }

    if (!Schema::hasTable('proveedores')) {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->unsignedBigInteger('municipalidad_id')->default(1);
            $table->timestamps();
        });
        $reporte[] = "✅ Tabla 'proveedores' creada.";
    }

    Schema::table('activos', function (Blueprint $table) use (&$reporte) {
        if (!Schema::hasColumn('activos', 'marca_id')) {
            $table->unsignedBigInteger('marca_id')->nullable()->after('id');
            $reporte[] = "✅ Columna 'marca_id' agregada a activos.";
        }
        if (!Schema::hasColumn('activos', 'color_id')) {
            $table->unsignedBigInteger('color_id')->nullable()->after('marca_id');
            $reporte[] = "✅ Columna 'color_id' agregada a activos.";
        }
        if (!Schema::hasColumn('activos', 'proveedor_id')) {
            $table->unsignedBigInteger('proveedor_id')->nullable()->after('color_id');
            $reporte[] = "✅ Columna 'proveedor_id' agregada a activos.";
        }
    });

    if (Schema::hasTable('users')) {
        Schema::table('users', function (Blueprint $table) use (&$reporte) {
            if (!Schema::hasColumn('users', 'rol')) {
                $table->string('rol')->default('operador')->after('email');
                $reporte[] = "✅ Columna 'rol' agregada a la tabla users.";
            }
        });
    }

    $adminEmail = 'admin@muni.com';
    $user = User::where('email', $adminEmail)->first();
    if ($user && $user->rol !== 'admin') {
        $user->rol = 'admin';
        $user->save();
        $reporte[] = "✅ Usuario {$adminEmail} actualizado a rol 'admin'.";
    }

    try {
        Artisan::call('filament:optimize-clear');
        $reporte[] = "⚡ Caché de Filament limpiada.";
    } catch (\Exception $e) {
        $reporte[] = "⚠️ Error en caché: " . $e->getMessage();
    }

    return count($reporte) > 0 ? implode("<br>", $reporte) : "La base de datos ya está al día.";
});

Route::get('/limpieza-profunda-prod', function () {
    $reporte = [];
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'railway');
        $colName = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            Schema::dropIfExists($table->$colName);
        }
        $reporte[] = "🗑️ FASE 1: Base de datos borrada.";

        Artisan::call('migrate --force');
        $reporte[] = "⚙️ FASE 2: Migraciones limpias ejecutadas.";

        if (Schema::hasTable('municipalidades')) {
            Schema::table('municipalidades', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('municipalidades', 'codigo_muni')) {
                    $table->string('codigo_muni', 50)->nullable()->after('id');
                    $reporte[] = "📝 FASE 3: Columna 'codigo_muni' inyectada.";
                }
            });
        }

        $muniIdPredeterminado = env('MUNICIPALIDAD_DEFAULT_ID', 1);
        Municipalidad::updateOrCreate(
            ['id' => $muniIdPredeterminado],
            [
                'nombre' => 'Municipalidad Destino Real',
                'codigo_muni' => '910',
                'departamento' => 'Principal',
            ]
        );
        $reporte[] = "🏢 FASE 4: Registro inicial insertado en 'municipalidades'.";

        User::updateOrCreate(
            ['email' => 'admin@muni.com'],
            [
                'name' => 'Admin Municipal',
                'password' => Hash::make('Muni2026*'),
                'rol' => 'admin',
            ]
        );
        $reporte[] = "👑 FASE 5: Usuario administrador configurado.";

        Artisan::call('filament:optimize-clear');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json(['status' => 'success', 'steps' => $reporte], 200);
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return response()->json(['status' => 'error', 'error_details' => $e->getMessage()], 500);
    }
});

Route::get('/limpiar-permisos', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return "⚡ Permisos reiniciados en caliente.";
});

// --- RUTA QUIRÚRGICA: EXPANDIR PREFIJO DE CATEGORÍAS A 100 ---
Route::get('/limpieza-profunda-jerarquia', function () {
    $reporte = [];
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // Modificamos la columna prefijo para que acepte 100 caracteres
        if (Schema::hasTable('categorias')) {
            DB::statement("ALTER TABLE categorias MODIFY COLUMN prefijo VARCHAR(100) NOT NULL;");
            $reporte[] = "🏷️ ¡Columna 'prefijo' ampliada exitosamente a 100 caracteres en la tabla categorias!";
        } else {
            $reporte[] = "❌ No se encontró la tabla 'categorias'.";
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        return response()->json([
            'status' => 'success',
            'message' => '🚀 ¡Estructura de categorías actualizada en producción!',
            'steps' => $reporte
        ], 200);

    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        return response()->json([
            'status' => 'error',
            'message' => '❌ No se pudo modificar el largo del prefijo.',
            'error_details' => $e->getMessage()
        ], 500);
    }
});