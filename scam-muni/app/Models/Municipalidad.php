<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipalidad extends Model
{
    protected $table = 'municipalidades'; // Indicamos el nombre correcto de la tabla
    protected $fillable = ['codigo_muni', 'nombre', 'nit', 'departamento', 'logo'];

    public function activos()
    {
        return $this->hasMany(Activo::class); // Una muni tiene muchos activos
    }
}