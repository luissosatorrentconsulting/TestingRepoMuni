<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    public function municipalidad() { return $this->belongsTo(Municipalidad::class); }
public function departamentos() { return $this->hasMany(Departamento::class); }
protected $fillable = ['nombre', 'municipalidad_id'];
}
