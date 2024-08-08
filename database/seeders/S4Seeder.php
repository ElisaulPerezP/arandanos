<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S4;

class S4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S4::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1 y valores específicos
        S4::factory()->create([
            'id' => 1,
            'estado' => 'inicial', // Asigna el estado inicial
            'comando_id' => null, // Asigna el comando_id inicial
            'pump3' => 'off', // Asigna el valor inicial de pump3
            'pump4' => 'off', // Asigna el valor inicial de pump4
        ]);
    }
}
