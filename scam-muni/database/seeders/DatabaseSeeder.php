<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
{
    // Creamos la municipalidad por defecto primero (ya que los usuarios/oficinas dependen de ella)
    $muni = \App\Models\Municipalidad::create([
        'id' => 1,
        'nombre' => 'Municipalidad de Prueba Local',
    ]);

    // Creamos tu usuario Administrador Maestro
    \App\Models\User::create([
        'name' => 'Administrador',
        'email' => 'admin@muni.com',
        'password' => bcrypt('12345'), // Cambia esto por tu clave
        'rol' => 'admin',
    ]);
}
}
