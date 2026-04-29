<?php




namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model


{
    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'municipalidad_id',
    ];

    // Aquí es donde "quemamos" el ID para que no lo pida en el formulario
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->municipalidad_id) {
                $model->municipalidad_id = env('MUNICIPALIDAD_DEFAULT_ID', 1);
            }
        });
    }
}