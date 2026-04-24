<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('gestiones_autoridades', function (Blueprint $table) {
        $table->id();
        $table->foreignId('empleado_id')->constrained('empleados'); // El empleado que ostenta el cargo
        $table->string('cargo'); // 'ALCALDE' o 'DIRECTOR_FINANCIERO'
        $table->date('fecha_inicio');
        $table->date('fecha_fin')->nullable(); // Si es null, es el actual
        $table->string('periodo_gestion')->nullable(); // Ej: "2024-2028"
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestiones_autoridades');
    }
};
