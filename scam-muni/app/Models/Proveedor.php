<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores'; 
   protected $fillable = [
    'nombre', 
    'nit', 
    'telefono', 
    'direccion', // <--- ESTE TIENE QUE ESTAR AQUÍ
    'municipalidad_id'
];

protected static function booted()
{
    static::addGlobalScope('muni', function (\Illuminate\Database\Eloquent\Builder $builder) {
        $builder->where('municipalidad_id', config('app.muni_id', 1));
    });

    static::creating(function ($model) {
        if (!$model->municipalidad_id) {
            $model->municipalidad_id = config('app.muni_id', 1);
        }
    });
}

public function activos()
{
    return $this->hasMany(Activo::class);
}

public function bienesVarios()
{
    return $this->hasMany(BienVario::class);
}
}


