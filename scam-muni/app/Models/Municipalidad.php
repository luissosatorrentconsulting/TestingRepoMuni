<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipalidad extends Model
{
    protected $table = 'municipalidades'; // Indicamos el nombre correcto de la tabla
    protected $fillable = ['codigo_muni', 'nombre', 'nit', 'departamento', 'logo'];

    public function activos()
    {
        return $this->hasMany(Activo::class); // Una muni tiene muchos activos
    }

    public function getAutoridadActual($cargo)
{
    return \App\Models\GestionAutoridad::where('cargo', $cargo)
        ->where('fecha_inicio', '<=', now())
        ->where(function ($query) {
            $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
        })
        ->first()?->empleado?->nombre_completo ?? 'No asignado';
}
}