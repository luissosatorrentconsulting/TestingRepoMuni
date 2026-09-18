<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';
    protected $fillable = ['nombre', 'observacion', 'municipalidad_id'];

    protected static function booted()
    {
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('ubicaciones.municipalidad_id', config('app.muni_id', 1));
        });

        static::creating(function ($ubicacion) {
            if (!$ubicacion->municipalidad_id) {
                $ubicacion->municipalidad_id = config('app.muni_id', 1);
            }
        });
    }
}