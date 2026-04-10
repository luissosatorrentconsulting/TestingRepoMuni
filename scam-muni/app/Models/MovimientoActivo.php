<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoActivo extends Model
{
    protected $table = 'movimientos_activos';
    
    protected $fillable = [
        'activo_id', 
        'entregado_por_id', 
        'recibido_por_id', 
        'fecha_movimiento', 
        'tipo_movimiento', 
        'observaciones'
    ];

    // AGREGA ESTO:
    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    public function activo() { return $this->belongsTo(Activo::class); }
    
    public function entregador() { return $this->belongsTo(Empleado::class, 'entregado_por_id'); }
    
    public function receptor() { return $this->belongsTo(Empleado::class, 'recibido_por_id'); }
}

