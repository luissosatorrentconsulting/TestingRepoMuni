<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Departamento extends Model
{
    // Cambiado: Ahora guarda municipalidad_id de forma directa
    protected $fillable = ['nombre', 'municipalidad_id', 'responsable_id'];

    // Un departamento pertenece directamente a la Municipalidad
    public function municipalidad()
    {
        return $this->belongsTo(Municipalidad::class);
    }

    // Un departamento ahora tiene muchas Oficinas (No puestos)
    public function oficinas()
    {
        return $this->hasMany(Oficina::class);
    }

    // Mantenemos tu Global Scope pero simplificado: directo a la Municipalidad
    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->where('municipalidad_id', config('app.muni_id', 1));
        });
    }
}