<?php
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use App\Models\Activo;
use App\Models\Municipalidad;
use App\Models\Empleado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
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