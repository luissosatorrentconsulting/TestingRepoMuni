<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear Marcas
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });

        // 2. Crear Colores
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });

        // 3. Crear Proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit')->nullable();
            $table->string('telefono')->nullable();
            $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
            $table->timestamps();
        });

        // 4. Modificar la tabla Activos (que ya existe)
        Schema::table('activos', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            $table->foreignId('color_id')->nullable()->constrained('colores');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
        });
    }

    public function down(): void {}
};