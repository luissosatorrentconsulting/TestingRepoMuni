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
    Schema::create('categorias', function (Blueprint $table) {
        $table->id();
        $table->string('prefijo', 10); // Ej: "MOB", "COM"
        $table->string('nombre', 100)->change(); // Si usas Laravel moderno, o simplemente pon:
        //$table->string('nombre');
        $table->decimal('porcentaje_depreciacion', 5, 2);
        $table->integer('ultimo_correlativo')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
