<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionAutoridad extends Model
{
    protected $table = 'gestiones_autoridades';
    protected $fillable = ['empleado_id', 'cargo', 'fecha_inicio', 'fecha_fin', 'periodo_gestion'];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}