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
    Schema::create('puestos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->foreignId('departamento_id')->constrained()->cascadeOnDelete();
        
        // El puesto "jefe" de este puesto
        // Ejemplo: El jefe del puesto "Auxiliar" es el puesto "Director"
        $table->unsignedBigInteger('puesto_padre_id')->nullable();
        $table->foreign('puesto_padre_id')->references('id')->on('puestos')->nullOnDelete();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puestos');
    }
};
