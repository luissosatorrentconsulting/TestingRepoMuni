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
    Schema::create('marcas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        // Aquí agregamos la relación con la muni, ya que cada muni 
        // podría querer sus propias marcas o compartirlas.
        $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
        $table->timestamps();

        $table->foreign('municipalidad_id')->references('id')->on('municipalidades')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};
