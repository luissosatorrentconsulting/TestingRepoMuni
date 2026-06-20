<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';
    protected $fillable = ['codigo', 'nombre', 'observacion', 'oficina_id'];

    // Una ubicación pertenece a una oficina
    public function oficina()
    {
        return $this->belongsTo(Oficina::class);
    }
}