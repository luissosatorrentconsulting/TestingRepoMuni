<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser; 
use Filament\Panel; 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    // Agregamos 'rol' para que Filament y la vista puedan guardarlo sin errores de MassAssignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'municipalidad_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Controlamos el acceso real según el rol que tengan guardado
        return $this->rol === 'admin' || $this->rol === 'operador';
    }

    public function municipalidad()
    {
        return $this->belongsTo(Municipalidad::class);
    }

    // Un Super Admin no pertenece a ninguna municipalidad fija: puede elegir
    // con cuál trabajar desde "Cambiar Municipalidad".
    public function isSuperAdmin(): bool
    {
        return is_null($this->municipalidad_id);
    }
}