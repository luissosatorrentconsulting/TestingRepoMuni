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
    static::creating(function ($model) {
        $model->municipalidad_id = env('MUNICIPALIDAD_DEFAULT_ID', 1);
    });
}
}


