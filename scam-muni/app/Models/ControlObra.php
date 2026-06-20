<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ControlObra extends Model
{
    protected $table = 'control_obras';
    protected $fillable = ['snip', 'nombre_proyecto', 'numero_contrato', 'monto', 'fecha', 'municipalidad_id'];

    // Vinculado a la municipalidad
    public function municipalidad()
    {
        return $this->belongsTo(Municipalidad::class);
    }

    // Global Scope Multi-Muni integrado
    protected static function booted()
    {
        static::addGlobalScope('municipalidad', function (Builder $builder) {
            $builder->where('municipalidad_id', config('app.muni_id', 1));
        });
    }
}