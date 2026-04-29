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
    Schema::table('activos', function (Blueprint $table) {
        $table->foreignId('marca_id')->nullable()->constrained('marcas')->onDelete('set null');
        $table->foreignId('color_id')->nullable()->constrained('colores')->onDelete('set null');
        $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            //
        });
    }
};
