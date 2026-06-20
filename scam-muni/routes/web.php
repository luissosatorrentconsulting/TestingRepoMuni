<?php
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use App\Models\Activo;
use App\Models\Municipalidad;
use App\Models\Empleado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint; // Esta es la línea que faltaba para evitar el error

Route::get('/', function () {
    return redirect('/admin');
});

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
    
    // CARGAMOS las relaciones jerárquicas del empleado
    $empleado->load(['puesto_oficial.departamento']);

    // Solo activos vigentes (que no sean baja)
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
    ])->setPaper('letter', 'landscape'); // <--- ESTO FUERZA LA HOJA HORIZONTAL

    return $pdf->stream("Traslados-{$empleado->nombre_completo}.pdf");
})->name('empleado.traslados.pdf');


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
        // Ejecuta las migraciones que faltan
        \Artisan::call('migrate --force');
        return "Tablas creadas con éxito: " . \Artisan::output();
    } catch (\Exception $e) {
        return "Error al migrar: " . $e->getMessage();
    }
});

Route::get('/limpiar-y-migrar', function() {
    try {
        // El comando 'migrate:fresh' borra todo lo que existe y lo crea desde cero correctamente
        \Artisan::call('migrate:fresh --force');
        
        // Opcional: Si tienes seeders para que la municipalidad aparezca de una vez
        // \Artisan::call('db:seed --force'); 
        
        return "Base de datos reconstruida con éxito. Por favor, vuelve a ejecutar la ruta /crear-usuario porque al borrar las tablas se borró el admin.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
Route::get('/reparar-todo', function() {
    try {
        // 1. Forzar desactivación de llaves a nivel global
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Obtener todas las tablas existentes
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'railway');
        $colName = "Tables_in_{$dbName}";

        // 3. Borrar cada tabla una por una
        foreach ($tables as $table) {
            Schema::dropIfExists($table->$colName);
        }

        // 4. Ejecutar las migraciones frescas
        Artisan::call('migrate --force');
        
        // 5. Reactivar llaves
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        return "✅ ¡LIMPIEZA TOTAL! Tablas creadas. Ahora ve a /crear-usuario para poder entrar.";
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
});

Route::get('/fix-db-prod', function () {
    $reporte = [];

    // 1. Crear Marcas
    if (!Schema::hasTable('marcas')) {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(1);
            $table->timestamps();
        });
        $reporte[] = "✅ Tabla 'marcas' creada.";
    }

    // 2. Crear Colores
    if (!Schema::hasTable('colores')) {
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(1);
            $table->timestamps();
        });
        $reporte[] = "✅ Tabla 'colores' creada.";
    }

    // 3. Crear Proveedores
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

    // 4. Agregar columnas a Activos (solo si no existen)
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

    return count($reporte) > 0 
        ? implode("<br>", $reporte) 
        : "No hubo cambios, la base de datos ya estaba actualizada.";
});


Route::get('/fix-db-prod', function () {
    $reporte = [];

    // 1. Agregar columna 'rol' a la tabla 'users' (solo si no existe)
    if (Schema::hasTable('users')) {
        Schema::table('users', function (Blueprint $table) use (&$reporte) {
            if (!Schema::hasColumn('users', 'rol')) {
                // Lo creamos como 'operador' por defecto para seguridad
                $table->string('rol')->default('operador')->after('email');
                $reporte[] = "✅ Columna 'rol' agregada a la tabla users.";
            }
        });
    }

    // 2. Asegurar que el usuario maestro sea ADMIN
    $adminEmail = 'admin@muni.com';
    $user = User::where('email', $adminEmail)->first();
    if ($user) {
        if ($user->rol !== 'admin') {
            $user->rol = 'admin';
            $user->save();
            $reporte[] = "✅ Usuario {$adminEmail} actualizado a rol 'admin'.";
        }
    } else {
        $reporte[] = "❌ Error: No se encontró al usuario {$adminEmail}.";
    }

    // 3. Limpiar optimización para que reconozca los nuevos Widgets y Resources
    try {
        Artisan::call('filament:optimize-clear');
        $reporte[] = "⚡ Caché de Filament limpiada y optimizada.";
    } catch (\Exception $e) {
        $reporte[] = "⚠️ No se pudo limpiar la caché de Filament: " . $e->getMessage();
    }

    return count($reporte) > 0 
        ? implode("<br>", $reporte) 
        : "No hubo cambios necesarios, la base de datos y permisos ya están al día.";
});

Route::get('/limpieza-profunda-prod', function () {
    $reporte = [];

    try {
        // FASE 1: Apagar llaves y borrar absolutamente todas las tablas existentes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'railway');
        $colName = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            Schema::dropIfExists($table->$colName);
        }
        $reporte[] = "🗑️ FASE 1: Base de datos borrada por completo (0 tablas residuales).";

        // FASE 2: Volver a ejecutar todas las migraciones limpias desde cero
        Artisan::call('migrate --force');
        $reporte[] = "⚙️ FASE 2: Migraciones ejecutadas con éxito en el orden correcto.";

        // FASE 3: Reparar e inyectar la columna 'codigo_muni' que agregamos manualmente
        if (Schema::hasTable('municipalidades')) {
            Schema::table('municipalidades', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('municipalidades', 'codigo_muni')) {
                    // La añadimos como VARCHAR para dar la máxima flexibilidad de códigos
                    $table->string('codigo_muni', 50)->nullable()->after('id');
                    $reporte[] = "📝 FASE 3: Columna 'codigo_muni' inyectada exitosamente en la tabla municipalidades.";
                }
            });
        }

        // FASE 4: Crear la primera fila base de la Municipalidad para que no falle el sistema
        $muniIdPredeterminado = env('MUNICIPALIDAD_DEFAULT_ID', 1);
        Municipalidad::updateOrCreate(
            ['id' => $muniIdPredeterminado],
            [
                'nombre' => 'Municipalidad Destino Real',
                'codigo_muni' => '910', // El código que estabas probando
                'departamento' => 'Principal',
            ]
        );
        $reporte[] = "🏢 FASE 4: Registro inicial insertado en 'municipalidades' (ID: {$muniIdPredeterminado}).";

        // FASE 5: Crear el superusuario administrador con su rol 'admin'
        User::updateOrCreate(
            ['email' => 'admin@muni.com'],
            [
                'name' => 'Admin Municipal',
                'password' => Hash::make('Muni2026*'), // Puedes cambiar la clave aquí
                'rol' => 'admin',
            ]
        );
        $reporte[] = "👑 FASE 5: Usuario 'admin@muni.com' configurado con rol 'admin' con éxito.";

        // FASE 6: Limpieza profunda de la caché interna de Filament
        Artisan::call('filament:optimize-clear');
        $reporte[] = "⚡ FASE 6: Caché de Filament purgada (listo para cargar recursos nuevos).";

        // Volvemos a activar las restricciones de llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Retornamos todo el éxito detallado en una lista limpia en pantalla
        return response()->json([
            'status' => 'success',
            'message' => '🚀 ¡DEPLOY DE BASE DE DATOS COMPLETADO CON ÉXITO!',
            'steps' => $reporte
        ], 200);

    } catch (\Exception $e) {
        // Si algo falla, forzamos el encendido de llaves para no romper la base de datos y vemos el error
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        return response()->json([
            'status' => 'error',
            'message' => '❌ Ocurrió un error en el despliegue automático.',
            'error_details' => $e->getMessage()
        ], 500);
    }
});