<?php
/*

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
public function up(): void
{
    Schema::create('municipalidades', function (Blueprint $table) {
        $table->id(); // ID interno
        $table->string('codigo_muni')->unique(); // Ej: "910"
        $table->string('nombre');
        $table->string('nit')->nullable();
        $table->timestamps();
    });
}

   
    public function down(): void
    {
        Schema::dropIfExists('municipalidades');
    }
};

*/


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lo dejamos vacío para que no intente crear la tabla que ya existe
    }

    public function down(): void
    {
        //
    }
};
