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
    Schema::create('activos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('municipalidad_id')->constrained('municipalidades');
        $table->foreignId('categoria_id')->constrained('categorias');
        
        $table->string('codigo_etiqueta')->unique(); 
        
        $table->string('descripcion');
        $table->string('marca')->nullable();
        $table->string('modelo')->nullable();
        $table->string('serie')->nullable();
        $table->decimal('costo_original', 15, 2);
        $table->date('fecha_compra');
        $table->decimal('valor_desecho', 15, 2)->default(0);
        
        $table->boolean('es_baja')->default(false);
        $table->string('numero_factura')->nullable();
$table->string('numero_inventario')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
