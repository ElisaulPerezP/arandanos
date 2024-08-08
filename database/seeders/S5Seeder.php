<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S5;

class S5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S5::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1 y valores específicos
        S5::factory()->create([
            'id' => 1,
            'estado' => 'inicial', // Asigna el estado inicial
            'comando_id' => null, // Asigna el comando_id inicial
            'flux1' => 0, // Asigna el valor inicial de flux1
            'flux2' => 0, // Asigna el valor inicial de flux2
        ]);
    }
}
