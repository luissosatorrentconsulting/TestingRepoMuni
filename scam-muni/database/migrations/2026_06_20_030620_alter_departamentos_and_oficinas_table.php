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
    // 1. Añadimos la columna padre a departamentos
    Schema::table('departamentos', function (Blueprint $table) {
        if (!Schema::hasColumn('departamentos', 'oficina_id')) {
            $table->unsignedBigInteger('oficina_id')->nullable()->after('id');
        }
    });

    // 2. Quitamos la columna vieja de oficinas si existía
    Schema::table('oficinas', function (Blueprint $table) {
        if (Schema::hasColumn('oficinas', 'departamento_id')) {
            $table->dropColumn('departamento_id');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
