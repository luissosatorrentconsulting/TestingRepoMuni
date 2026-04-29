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
    Schema::create('colores', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->unsignedBigInteger('municipalidad_id')->default(env('MUNICIPALIDAD_DEFAULT_ID', 1));
        $table->timestamps();
        $table->foreign('municipalidad_id')->references('id')->on('municipalidades');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
