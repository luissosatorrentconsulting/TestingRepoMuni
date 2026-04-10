<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    // Agregamos 'puesto_id' y mantenemos los demás
    protected $fillable = [
        'municipalidad_id', 
        'nombre_completo', 
        'dpi', 
        'puesto_id', // Relación con la nueva tabla
        'puesto',    // Lo dejamos por si aún tienes datos viejos ahí
        'activo'
    ];

    protected static function booted()
    {
        parent::booted();

        // Solo ver empleados de MI municipalidad
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('municipalidad_id', config('app.muni_id', 1));
        });

        // Al crear un empleado, asignarle MI municipalidad automáticamente
        static::creating(function ($empleado) {
            $empleado->municipalidad_id = config('app.muni_id', 1);
        });
    }

    // RELACIONES
    
    public function municipalidad(): BelongsTo
    { 
        return $this->belongsTo(Municipalidad::class); 
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    // NUEVA: Relación con el Puesto oficial
    public function puesto_oficial(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }
}