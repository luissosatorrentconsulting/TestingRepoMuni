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
    Schema::table('puestos', function (Blueprint $table) {
        // Eliminamos la relación anterior si existe
        $table->dropForeign(['puesto_padre_id']);
        $table->dropColumn('puesto_padre_id');

        // Creamos la nueva relación hacia Empleados
        $table->foreignId('empleado_jefe_id')
            ->nullable()
            ->constrained('empleados')
            ->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            //
        });
    }
};
