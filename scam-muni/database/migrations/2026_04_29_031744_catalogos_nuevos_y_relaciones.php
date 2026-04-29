<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    // 1. Crear Marcas si no existe
    if (!Schema::hasTable('marcas')) {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });
    }

    // 2. Crear Colores si no existe
    if (!Schema::hasTable('colores')) {
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });
    }

    // 3. Crear Proveedores si no existe
    if (!Schema::hasTable('proveedores')) {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit')->nullable();
            $table->string('telefono')->nullable();
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });
    }

    // 4. Agregar columnas a Activos (MODIFICACIÓN sobre lo existente)
    Schema::table('activos', function (Blueprint $table) {
        if (!Schema::hasColumn('activos', 'marca_id')) {
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->onDelete('set null');
        }
        if (!Schema::hasColumn('activos', 'color_id')) {
            $table->foreignId('color_id')->nullable()->constrained('colores')->onDelete('set null');
        }
        if (!Schema::hasColumn('activos', 'proveedor_id')) {
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
