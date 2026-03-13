<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    protected $fillable = ['activo_id', 'empleado_id', 'fecha_asignacion', 'documento_respaldo', 'observaciones'];

    protected static function booted()
    {
        parent::booted();

        // Solo ver asignaciones de activos que pertenecen a mi muni
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->whereHas('activo', function ($query) {
                $query->where('municipalidad_id', config('app.muni_id', 1));
            });
        });
    }

    public function activo() { return $this->belongsTo(Activo::class); }
    public function empleado() { return $this->belongsTo(Empleado::class); }
}