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
    Schema::create('asignaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('activo_id')->constrained('activos');
        $table->foreignId('empleado_id')->constrained('empleados');
        $table->date('fecha_asignacion');
        $table->string('documento_respaldo')->nullable(); // Para el número de acta
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
