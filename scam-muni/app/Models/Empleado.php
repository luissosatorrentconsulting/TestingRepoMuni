<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Empleado extends Model
{
    protected $fillable = ['municipalidad_id', 'nombre_completo', 'dpi', 'puesto', 'activo'];

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

    public function municipalidad() { return $this->belongsTo(Municipalidad::class); }
    public function asignaciones()
{
    return $this->hasMany(Asignacion::class);
}
}