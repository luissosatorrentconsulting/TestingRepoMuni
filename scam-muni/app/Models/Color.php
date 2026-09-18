<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
protected $table = 'colores'; // Laravel buscaría "colors", así que esto es obligatorio.
protected $fillable = ['nombre', 'municipalidad_id'];

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
}