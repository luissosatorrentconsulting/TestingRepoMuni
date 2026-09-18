<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MovimientoActivo extends Model
{
    protected $table = 'movimientos_activos';

    protected $fillable = [
        'activo_id',
        'entregado_por_id',
        'recibido_por_id',
        'fecha_movimiento',
        'tipo_movimiento',
        'observaciones',
        'municipalidad_id',
    ];

    // AGREGA ESTO:
    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('municipalidad_id', config('app.muni_id', 1));
        });

        static::creating(function ($movimiento) {
            if (!$movimiento->municipalidad_id) {
                $movimiento->municipalidad_id = config('app.muni_id', 1);
            }
        });
    }

    public function activo() { return $this->belongsTo(Activo::class); }
    
    public function entregador() { return $this->belongsTo(Empleado::class, 'entregado_por_id'); }
    
    public function receptor() { return $this->belongsTo(Empleado::class, 'recibido_por_id'); }
}

