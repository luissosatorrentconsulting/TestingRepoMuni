<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_obras', function (Blueprint $table) {
            $table->id();
            $table->string('snip')->unique()->comment('Código Único SNIP');
            $table->string('nombre_proyecto');
            $table->string('numero_contrato')->nullable();
            $table->decimal('monto', 12, 2)->default(0.00);
            $table->date('fecha')->nullable();

            // Relación directa con la Municipalidad
            $table->foreignId('municipalidad_id')->constrained('municipalidades')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_obras');
    }
};