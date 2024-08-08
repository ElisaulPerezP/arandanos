<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S0;

class S0Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elimina cualquier registro existente con ID 1 para evitar conflictos
        S0::where('id', 1)->delete();

        // Usar la factory para crear un registro con ID 1
        S0::factory()->create([
            'id' => 1,
            'estado' => false,
            'comando_id' => null,
            'sensor3' => 1,
        ]);
    }
}
