<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienes_varios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_qr')->nullable();
            $table->string('descripcion');
            $table->string('marca')->nullable();
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->date('fecha_compra')->nullable();
            $table->string('numero_factura')->nullable();
            $table->string('numero_inventario')->nullable();
            $table->string('estado')->default('Excelente'); // Excelente, Bueno, Regular, Malo
            $table->date('fecha_baja')->nullable();

            // Relaciones requeridas
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->foreignId('oficina_id')->constrained('oficinas')->onDelete('cascade'); // Ligado directo a la oficina
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienes_varios');
    }
};