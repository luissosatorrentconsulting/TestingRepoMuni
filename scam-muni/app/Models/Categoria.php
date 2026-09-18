<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Categoria extends Model
{
    protected $fillable = ['prefijo', 'nombre', 'porcentaje_depreciacion', 'ultimo_correlativo', 'municipalidad_id'];

    protected static function booted()
    {
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('categorias.municipalidad_id', config('app.muni_id', 1));
        });

        static::creating(function ($categoria) {
            if (!$categoria->municipalidad_id) {
                $categoria->municipalidad_id = config('app.muni_id', 1);
            }
        });
    }

    public function activos()
    {
        return $this->hasMany(Activo::class);
    }
}