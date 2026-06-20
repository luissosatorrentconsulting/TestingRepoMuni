<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Oficina extends Model
{
    // Agregamos 'ubicacion_id' al fillable
    protected $fillable = ['nombre', 'departamento_id', 'ubicacion_id'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function puestos()
    {
        return $this->hasMany(Puesto::class);
    }

    // NUEVA RELACIÓN: Una oficina tiene muchas ubicaciones detalladas
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class);
    }

    // NUEVA RELACIÓN: Una oficina puede marcar una ubicación como su sede/principal
    public function ubicacionPrincipal()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->whereHas('departamento', function ($query) {
                $query->where('municipalidad_id', config('app.muni_id', 1));
            });
        });
    }
}