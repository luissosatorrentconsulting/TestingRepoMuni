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
    Schema::create('movimientos_activos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('activo_id')->constrained('activos')->onDelete('cascade');
        
        // Quién entrega: Puede ser NULL (para la primera compra) o un Empleado
        $table->foreignId('entregado_por_id')->nullable()->constrained('empleados');
        
        // Quién recibe: Siempre es un Empleado
        $table->foreignId('recibido_por_id')->constrained('empleados');
        
        $table->dateTime('fecha_movimiento');
        $table->string('tipo_movimiento')->default('traslado'); // compra, traslado, devolucion
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_activos');
    }
};
