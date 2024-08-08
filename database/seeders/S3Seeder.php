<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S3;

class S3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S3::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1 y valores específicos
        S3::factory()->create([
            'id' => 1,
            'estado' => 'inicial', // Asigna el estado inicial
            'comando_id' => null, // Asigna el comando_id inicial
            'pump1' => 'off', // Asigna el valor inicial de pump1
            'pump2' => 'off', // Asigna el valor inicial de pump2
        ]);
    }
}
