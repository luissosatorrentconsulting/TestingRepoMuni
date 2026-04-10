<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puesto extends Model
{
    // Solo un fillable con los campos reales de tu tabla actual
    protected $fillable = [
        'nombre', 
        'departamento_id', 
        'empleado_jefe_id'
    ];

    // Relación con el Departamento
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    // Relación con el JEFE (que ahora es un Empleado directamente)
    public function jefe(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_jefe_id');
    }
}