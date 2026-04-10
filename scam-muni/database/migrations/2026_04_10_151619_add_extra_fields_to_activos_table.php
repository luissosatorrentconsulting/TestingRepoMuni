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
        $table->string('color')->nullable()->after('serie');
        $table->string('estado')->default('BUENO')->after('color');
        $table->text('observaciones_activo')->nullable()->after('estado');
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
