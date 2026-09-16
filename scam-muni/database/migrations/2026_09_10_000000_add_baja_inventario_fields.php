<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            if (!Schema::hasColumn('activos', 'fecha_baja')) {
                $table->date('fecha_baja')->nullable()->after('es_baja');
            }
            if (!Schema::hasColumn('activos', 'motivo_baja')) {
                $table->text('motivo_baja')->nullable()->after('fecha_baja');
            }
            if (!Schema::hasColumn('activos', 'no_suma_inventario')) {
                $table->boolean('no_suma_inventario')->default(false)->after('numero_inventario');
            }
        });

        Schema::table('bienes_varios', function (Blueprint $table) {
            if (!Schema::hasColumn('bienes_varios', 'marca_id')) {
                $table->foreignId('marca_id')->nullable()->after('marca')->constrained('marcas')->onDelete('set null');
            }
            if (!Schema::hasColumn('bienes_varios', 'no_suma_inventario')) {
                $table->boolean('no_suma_inventario')->default(false)->after('numero_inventario');
            }
        });

        Schema::table('asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('asignaciones', 'activa')) {
                $table->boolean('activa')->default(true)->after('observaciones');
            }
        });

        // Bug preexistente: Categoria::$fillable incluye 'nombre' pero la tabla
        // nunca tuvo esa columna, por lo que crear una categoría fallaba.
        Schema::table('categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias', 'nombre')) {
                $table->string('nombre')->default('')->after('prefijo');
            }
        });

        // Bug preexistente: 'codigo_muni' (usado para generar el código de cada
        // activo) nunca quedó en una migración base, solo en una ruta puntual.
        Schema::table('municipalidades', function (Blueprint $table) {
            if (!Schema::hasColumn('municipalidades', 'codigo_muni')) {
                $table->string('codigo_muni', 50)->nullable()->after('id');
            }
        });

        // Bug preexistente: Empleado::$fillable y el formulario usan 'puesto_id'
        // (relación con la nueva tabla puestos), pero ninguna migración lo creó.
        // Ya existe en producción (se agregó a mano en algún momento), así que
        // esto solo importa para levantar un entorno nuevo desde cero.
        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'puesto_id')) {
                $table->unsignedBigInteger('puesto_id')->nullable()->after('dpi');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
