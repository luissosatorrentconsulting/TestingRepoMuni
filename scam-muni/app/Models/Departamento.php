<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Departamento extends Model
{
    protected $fillable = ['nombre', 'oficina_id', 'responsable_id'];

    // Relación con Oficina
    public function oficina()
    {
        return $this->belongsTo(Oficina::class);
    }

    // Relación con Puestos
    public function puestos()
    {
        return $this->hasMany(Puesto::class);
    }

    // ESTO ES CLAVE: Filtro automático para que solo salgan departamentos 
    // que pertenezcan a oficinas de la municipalidad actual.
    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->whereHas('oficina', function ($query) {
                $query->where('municipalidad_id', config('app.muni_id', 1));
            });
        });
    }
}