<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            if (!Schema::hasColumn('activos', 'acta_baja')) {
                $table->string('acta_baja')->nullable()->after('fecha_baja');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
