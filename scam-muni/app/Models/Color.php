<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
protected $table = 'colores'; // Laravel buscaría "colors", así que esto es obligatorio.
protected $fillable = ['nombre', 'municipalidad_id'];

protected static function booted()
{
    static::creating(function ($model) {
        $model->municipalidad_id = env('MUNICIPALIDAD_DEFAULT_ID', 1);
    });
}
}