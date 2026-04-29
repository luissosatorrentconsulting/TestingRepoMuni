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
    Schema::table('proveedores', function (Blueprint $table) {
        // Agregamos la columna direccion después de telefono
        if (!Schema::hasColumn('proveedores', 'direccion')) {
            $table->string('direccion')->nullable()->after('telefono');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            //
        });
    }
};
