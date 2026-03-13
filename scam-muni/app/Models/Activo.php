<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Activo extends Model
{
    protected $fillable = [
        'municipalidad_id', 'categoria_id', 'codigo_etiqueta', 
        'descripcion', 'marca', 'modelo', 'serie', 
        'costo_original', 'fecha_compra', 'valor_desecho', 'es_baja'
    ];

    // ESTA ES LA MAGIA MULTI-MUNI
    protected static function booted()
    {
        // 1. Filtro Global: Cada vez que alguien pida ver activos, 
        // Laravel solo traerá los de la muni configurada.
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('municipalidad_id', config('app.muni_id', 1));
        });

        // 2. Asignación automática: Al crear, le ponemos la muni sin preguntar.
        static::creating(function ($activo) {
            $activo->municipalidad_id = config('app.muni_id', 1);
            
            // Lógica del código (910-MOB-0001)
            $muni = Municipalidad::find($activo->municipalidad_id);
            $cat = Categoria::find($activo->categoria_id);
            $cat->increment('ultimo_correlativo');
            $nuevoCorrelativo = str_pad($cat->ultimo_correlativo, 4, '0', STR_PAD_LEFT);
            $activo->codigo_etiqueta = "{$muni->codigo_muni}-{$cat->prefijo}-{$nuevoCorrelativo}";
        });
    }

    public function municipalidad() { return $this->belongsTo(Municipalidad::class); }
    public function categoria() { return $this->belongsTo(Categoria::class); }
}