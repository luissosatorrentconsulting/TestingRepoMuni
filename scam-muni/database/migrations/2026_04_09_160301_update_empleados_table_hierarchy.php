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
    Schema::table('empleados', function (Blueprint $table) {
        // El empleado solo sabe qué puesto ocupa. 
        // El sistema "deducirá" quién es su jefe viendo quién ocupa el 'puesto_padre_id'
        $table->foreignId('puesto_id')->nullable()->constrained();
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
