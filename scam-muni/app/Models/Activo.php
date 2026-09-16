<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <--- AGREGADO
use Illuminate\Database\Eloquent\Relations\HasMany;  // <--- AGREGADO

class Activo extends Model
{
    protected $fillable = [
        'municipalidad_id', 'categoria_id', 'codigo_etiqueta',
        'descripcion', 'marca', 'modelo', 'serie',
        'costo_original', 'fecha_compra', 'valor_desecho', 'es_baja',
        'marca_id', 'color_id', 'proveedor_id', 'estado', 'observaciones_activo',
        'numero_factura', 'numero_inventario', 'no_suma_inventario',
        'fecha_baja', 'motivo_baja',
    ];

    protected $casts = [
        'es_baja' => 'boolean',
        'no_suma_inventario' => 'boolean',
        'fecha_baja' => 'date',
    ];

    // ESTA ES LA MAGIA MULTI-MUNI
    protected static function booted()
    {
        // 1. Filtro Global: Laravel solo traerá los de la muni configurada.
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

    public function municipalidad(): BelongsTo 
    { 
        return $this->belongsTo(Municipalidad::class); 
    }

    public function categoria(): BelongsTo 
    { 
        return $this->belongsTo(Categoria::class); 
    }
    
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function asignacionActiva()
    {
        return $this->hasOne(Asignacion::class)->where('activa', true);
    }

    // Nombrado "marcaInfo"/"colorInfo" (no "marca"/"color") porque esas columnas
    // string legadas siguen existiendo en la tabla y Eloquent siempre resuelve
    // el atributo por encima de la relación cuando comparten nombre.
    public function marcaInfo()
{
    return $this->belongsTo(Marca::class, 'marca_id');
}

public function colorInfo()
{
    return $this->belongsTo(Color::class, 'color_id');
}

public function proveedor()
{
    return $this->belongsTo(Proveedor::class);
}


public function historial()
{
    return $this->hasMany(MovimientoActivo::class, 'activo_id')->orderBy('fecha_movimiento', 'asc');
}

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('es_baja', false)
            ->whereDoesntHave('asignaciones', fn (Builder $q) => $q->where('activa', true));
    }

}