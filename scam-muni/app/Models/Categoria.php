<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['prefijo', 'nombre', 'porcentaje_depreciacion', 'ultimo_correlativo'];

    public function activos()
    {
        return $this->hasMany(Activo::class);
    }
}