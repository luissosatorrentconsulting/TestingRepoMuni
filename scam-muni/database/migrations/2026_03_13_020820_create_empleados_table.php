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
    Schema::create('empleados', function (Blueprint $table) {
        $table->id();
        $table->foreignId('municipalidad_id')->constrained('municipalidades'); // Cada empleado pertenece a una muni
        $table->string('nombre_completo');
        $table->string('dpi')->unique();
        $table->string('puesto');
        $table->boolean('activo')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
