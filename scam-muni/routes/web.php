<?php

use App\Models\User;
use App\Models\Activo;
use App\Models\Asignacion;
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

// El middleware 'auth' de Laravel redirige a la ruta nombrada 'login' cuando
// no hay sesión. El login real vive en Filament (/admin/login) bajo otro
// nombre de ruta, así que se registra este alias para que el redirect funcione.
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// --- SEGURIDAD DE RUTAS "ROMPER VIDRIO" ---
// Las rutas de la sección final de este archivo pueden borrar TODA la base de
// datos o resetear el usuario admin. Son justamente las que hace falta poder
// correr cuando el sistema está roto o recién reseteado (sin ningún usuario
// con el que iniciar sesión todavía), así que no pueden exigir login: se
// protegen con un token secreto en la URL.
//
// Configurá MAINTENANCE_TOKEN en las variables de entorno del servidor (no
// dejes el valor por defecto de tu .env local) y usá esas rutas como
// /reparar-todo?token=TU_TOKEN
if (!function_exists('requireMaintenanceToken')) {
    // routes/web.php puede cargarse más de una vez en el mismo proceso (por
    // ejemplo, al correr todo el suite de tests junto), así que la
    // declaración de la función se protege para no romper con "Cannot
    // redeclare function".
    function requireMaintenanceToken(): void
    {
        $token = request()->query('token', '');
        $esperado = env('MAINTENANCE_TOKEN', '');

        abort_if($esperado === '', 500, 'MAINTENANCE_TOKEN no está configurado en el servidor.');
        abort_unless(hash_equals($esperado, (string) $token), 403, 'Acceso denegado: falta o es incorrecto el token de mantenimiento.');
    }
}

// --- TODO LO DEMÁS REQUIERE SESIÓN INICIADA (mismo guard que /admin) ---
Route::middleware('auth')->group(function () {

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
        $empleado->load(['puesto_oficial.oficina.departamento']);

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
        $empleado->load(['puesto_oficial.oficina.departamento']);

        $traslados = \App\Models\MovimientoActivo::where('entregado_por_id', $empleado->id)
            ->orWhere('recibido_por_id', $empleado->id)
            ->with(['activo.categoria', 'entregador', 'receptor'])
            ->orderBy('fecha_movimiento', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.traslados_empleado', [
            'empleado' => $empleado,
            'muni' => $muni,
            'traslados' => $traslados
        ])->setPaper('letter', 'landscape');

        return $pdf->stream("Traslados-{$empleado->nombre_completo}.pdf");
    })->name('empleado.traslados.pdf');

    // Acta de Asignación (antes era una descarga sin ruta propia; se le da
    // nombre para poder enlazarla igual que los demás reportes)
    Route::get('/asignacion/{asignacion}/acta-pdf', function (Asignacion $asignacion) {
        $muni = Municipalidad::find(config('app.muni_id', 1));

        $pdf = Pdf::loadView('pdf.acta_asignacion', [
            'asignacion' => $asignacion,
            'muni' => $muni,
        ]);

        return $pdf->stream("Acta-{$asignacion->id}.pdf");
    })->name('asignacion.acta.pdf');

    // Reporte General de Inventario, sin depender de los filtros de la tabla
    // (la acción "Reporte de Inventario" dentro de Activos sigue existiendo
    // tal cual y respeta lo que esté filtrado ahí; esta ruta es la versión
    // "todo el inventario" para usar desde la Central de Reportes).
    Route::get('/reportes/inventario-general-pdf', function () {
        $muni = Municipalidad::find(config('app.muni_id', 1));
        $activos = Activo::where('es_baja', false)->get();

        $pdf = Pdf::loadView('pdf.inventario_general', [
            'activos' => $activos,
            'muni' => $muni,
        ]);

        return $pdf->stream('Inventario-' . now()->format('d-m-Y') . '.pdf');
    })->name('reportes.inventario-general.pdf');


    // --- RUTAS DE MANTENIMIENTO INCREMENTAL (asumen que la app ya funciona) ---

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

    Route::get('/limpiar-permisos', function () {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        return "⚡ Permisos reiniciados en caliente.";
    });

    // RUTA DE MANTENIMIENTO: Cambios de BD para Fase 2 (baja, inventario, reasignación)
    Route::get('/fix-db-fase2', function () {
        $reporte = [];

        Schema::table('activos', function (Blueprint $table) use (&$reporte) {
            if (!Schema::hasColumn('activos', 'fecha_baja')) {
                $table->date('fecha_baja')->nullable()->after('es_baja');
                $reporte[] = "✅ Columna 'fecha_baja' agregada a activos.";
            }
            if (!Schema::hasColumn('activos', 'motivo_baja')) {
                $table->text('motivo_baja')->nullable()->after('fecha_baja');
                $reporte[] = "✅ Columna 'motivo_baja' agregada a activos.";
            }
            if (!Schema::hasColumn('activos', 'no_suma_inventario')) {
                $table->boolean('no_suma_inventario')->default(false)->after('numero_inventario');
                $reporte[] = "✅ Columna 'no_suma_inventario' agregada a activos.";
            }
        });

        if (Schema::hasTable('bienes_varios')) {
            Schema::table('bienes_varios', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('bienes_varios', 'marca_id')) {
                    $table->foreignId('marca_id')->nullable()->after('marca')->constrained('marcas')->onDelete('set null');
                    $reporte[] = "✅ Columna 'marca_id' agregada a bienes_varios.";
                }
                if (!Schema::hasColumn('bienes_varios', 'no_suma_inventario')) {
                    $table->boolean('no_suma_inventario')->default(false)->after('numero_inventario');
                    $reporte[] = "✅ Columna 'no_suma_inventario' agregada a bienes_varios.";
                }
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('categorias', 'nombre')) {
                    $table->string('nombre')->default('')->after('prefijo');
                    $reporte[] = "✅ Columna 'nombre' agregada a categorias (bug preexistente: faltaba desde siempre).";
                }
            });
        }

        if (Schema::hasTable('municipalidades')) {
            Schema::table('municipalidades', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('municipalidades', 'codigo_muni')) {
                    $table->string('codigo_muni', 50)->nullable()->after('id');
                    $reporte[] = "✅ Columna 'codigo_muni' agregada a municipalidades.";
                }
                if (!Schema::hasColumn('municipalidades', 'nit')) {
                    $table->string('nit')->nullable()->after('nombre');
                    $reporte[] = "✅ Columna 'nit' agregada a municipalidades (bug preexistente: la migración original quedó comentada).";
                }
            });
        }

        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('empleados', 'puesto_id')) {
                    $table->unsignedBigInteger('puesto_id')->nullable()->after('dpi');
                    $reporte[] = "✅ Columna 'puesto_id' agregada a empleados.";
                }
            });
        }

        if (Schema::hasTable('asignaciones')) {
            Schema::table('asignaciones', function (Blueprint $table) use (&$reporte) {
                if (!Schema::hasColumn('asignaciones', 'activa')) {
                    $table->boolean('activa')->default(true)->after('observaciones');
                    $reporte[] = "✅ Columna 'activa' agregada a asignaciones.";
                }
            });

            if (Schema::hasColumn('asignaciones', 'activa')) {
                // MySQL no permite hacer UPDATE de una tabla usando una subconsulta
                // sobre esa misma tabla ("target table for update in FROM clause"),
                // así que resolvemos los IDs vigentes en PHP primero.
                $idsVigentes = DB::table('asignaciones')
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('activo_id')
                    ->pluck('id');

                DB::table('asignaciones')->whereIn('id', $idsVigentes)->update(['activa' => true]);
                DB::table('asignaciones')->whereNotIn('id', $idsVigentes)->update(['activa' => false]);

                $reporte[] = "✅ Backfill de 'activa': solo la asignación más reciente de cada activo queda marcada como vigente.";
            }
        }

        // Borrado suave para Activos, Empleados y Asignaciones: hoy un
        // "Eliminar" es permanente e irrecuperable.
        foreach (['activos', 'empleados', 'asignaciones'] as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) use ($tabla, &$reporte) {
                if (!Schema::hasColumn($tabla, 'deleted_at')) {
                    $table->softDeletes();
                    $reporte[] = "✅ Borrado suave habilitado en '{$tabla}' (columna 'deleted_at').";
                }
            });
        }

        try {
            Artisan::call('filament:optimize-clear');
            $reporte[] = "⚡ Caché de Filament limpiada.";
        } catch (\Exception $e) {
            $reporte[] = "⚠️ Error en caché: " . $e->getMessage();
        }

        return count($reporte) > 0 ? implode("<br>", $reporte) : "La base de datos ya está al día (fase 2).";
    });

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
});

// --- RUTAS "ROMPER VIDRIO" (requieren ?token=MAINTENANCE_TOKEN, no login) ---
// Úsalas así: https://tu-dominio/reparar-todo?token=TU_TOKEN

Route::get('/crear-usuario', function () {
    requireMaintenanceToken();

    $user = User::updateOrCreate(
        ['email' => 'admin@muni.com'],
        [
            'name' => 'Admin Municipal',
            'password' => Hash::make('Muni2026*'),
        ]
    );
    return "Usuario creado o actualizado con éxito. Ya puedes ir a /admin";
});

Route::get('/migrar-todo', function () {
    requireMaintenanceToken();

    try {
        Artisan::call('migrate --force');
        return "Tablas creadas con éxito: " . Artisan::output();
    } catch (\Exception $e) {
        return "Error al migrar: " . $e->getMessage();
    }
});

Route::get('/limpiar-y-migrar', function () {
    requireMaintenanceToken();

    try {
        Artisan::call('migrate:fresh --force');
        return "Base de datos reconstruida con éxito. Por favor, vuelve a ejecutar la ruta /crear-usuario porque al borrar las tablas se borró el admin.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/reparar-todo', function () {
    requireMaintenanceToken();

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

Route::get('/limpieza-profunda-prod', function () {
    requireMaintenanceToken();

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
