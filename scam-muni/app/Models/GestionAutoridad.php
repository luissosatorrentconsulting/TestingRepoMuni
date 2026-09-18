<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GestionAutoridad extends Model
{
    protected $table = 'gestiones_autoridades';
    protected $fillable = ['empleado_id', 'cargo', 'fecha_inicio', 'fecha_fin', 'periodo_gestion', 'municipalidad_id'];

    protected static function booted()
    {
        static::addGlobalScope('muni', function (Builder $builder) {
            $builder->where('gestiones_autoridades.municipalidad_id', config('app.muni_id', 1));
        });

        static::creating(function ($gestion) {
            if (!$gestion->municipalidad_id) {
                $gestion->municipalidad_id = config('app.muni_id', 1);
            }
        });
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}