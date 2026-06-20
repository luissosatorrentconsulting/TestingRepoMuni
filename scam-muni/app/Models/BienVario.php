<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienVario extends Model
{
    protected $table = 'bienes_varios';
    protected $fillable = [
        'codigo_qr', 'descripcion', 'marca', 'costo', 
        'fecha_compra', 'numero_factura', 'numero_inventario', 
        'estado', 'fecha_baja', 'categoria_id', 'proveedor_id', 'oficina_id'
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
}