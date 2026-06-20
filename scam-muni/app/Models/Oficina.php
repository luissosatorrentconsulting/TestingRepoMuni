<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Oficina extends Model
{
    // Cambiado: Su padre ahora es el Departamento
    protected $fillable = ['nombre', 'departamento_id'];

    // Una oficina pertenece a un Departamento
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // Una oficina ahora tiene muchos Puestos
    public function puestos()
    {
        return $this->hasMany(Puesto::class);
    }

    // Agregamos Global Scope para heredar la seguridad Multi-Muni a través del departamento
    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->whereHas('departamento', function ($query) {
                $query->where('municipalidad_id', config('app.muni_id', 1));
            });
        });
    }
}