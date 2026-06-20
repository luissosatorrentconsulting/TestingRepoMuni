<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('observacion')->nullable();
            // BORRAMOS la línea de oficina_id de aquí para romper el bucle
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ubicaciones'); }
};