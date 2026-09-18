<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Habilita el sistema para operar como multi-tenant real: cada usuario
     * queda asociado a una municipalidad (o a ninguna, si es Super Admin), y
     * las tablas que hoy comparten catálogo entre "todas las municipalidades"
     * (porque nunca se les agregó el filtro) pasan a aislarse igual que
     * Activos/Empleados.
     */
    public function up(): void
    {
        // Usuarios: null = Super Admin (puede operar en cualquier municipalidad).
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'municipalidad_id')) {
                $table->unsignedBigInteger('municipalidad_id')->nullable()->after('id');
            }
        });

        foreach (['categorias', 'bienes_varios', 'gestiones_autoridades', 'ubicaciones', 'movimientos_activos'] as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (!Schema::hasColumn($tabla, 'municipalidad_id')) {
                    $table->unsignedBigInteger('municipalidad_id')->nullable()->after('id');
                }
            });
        }

        // Backfill: todo lo que existe hoy pertenece a la única municipalidad
        // que había hasta ahora. OJO: no asumimos que su id es 1 — en algunos
        // entornos se creó con env('MUNICIPALIDAD_DEFAULT_ID'), que puede ser
        // distinto. Lo resolvemos de la fuente real en vez de adivinar.
        $municipalidadExistente = DB::table('municipalidades')->orderBy('id')->value('id')
            ?? DB::table('activos')->whereNotNull('municipalidad_id')->orderBy('municipalidad_id')->value('municipalidad_id')
            ?? DB::table('empleados')->whereNotNull('municipalidad_id')->orderBy('municipalidad_id')->value('municipalidad_id')
            ?? 1;

        foreach (['categorias', 'bienes_varios', 'gestiones_autoridades', 'ubicaciones', 'movimientos_activos', 'users'] as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'municipalidad_id')) {
                DB::table($tabla)->whereNull('municipalidad_id')->update(['municipalidad_id' => $municipalidadExistente]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
