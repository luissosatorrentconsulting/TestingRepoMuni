<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Puesto extends Model
{
    // Cambiado: Su padre ahora es la Oficina
    protected $fillable = [
        'nombre', 
        'oficina_id', 
        'empleado_jefe_id'
    ];

    // Un puesto pertenece a una Oficina
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_jefe_id');
    }

    // Agregamos Global Scope para que el puesto solo salga si su oficina y departamento son de la muni actual
    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->whereHas('oficina.departamento', function ($query) {
                $query->where('municipalidad_id', config('app.muni_id', 1));
            });
        });
    }
}