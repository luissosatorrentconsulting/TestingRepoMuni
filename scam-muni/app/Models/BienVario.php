<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienVario extends Model
{
    protected $table = 'bienes_varios';
    protected $fillable = [
        'codigo_qr', 'descripcion', 'marca', 'marca_id', 'costo',
        'fecha_compra', 'numero_factura', 'numero_inventario', 'no_suma_inventario',
        'estado', 'fecha_baja', 'categoria_id', 'proveedor_id', 'oficina_id'
    ];

    protected $casts = [
        'no_suma_inventario' => 'boolean',
        'fecha_baja' => 'date',
    ];

    // Relación directa con Oficina (No con Empleado)
    public function oficina()
    {
        return $this->belongsTo(Oficina::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // "marcaInfo" (no "marca") por la misma razón que en Activo: existe una
    // columna string "marca" legada que colisionaría con el nombre de la relación.
    public function marcaInfo()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }
}