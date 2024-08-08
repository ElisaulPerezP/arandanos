<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S0;
use App\Models\S1;
use App\Models\S2;
use App\Models\S3;
use App\Models\S4;
use App\Models\S5;
use App\Models\EstadoSistema;

class EstadoSistemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear registros en s0 a s5 con ID 1 y valores conocidos si no existen
        $s0 = S0::firstOrCreate(
            ['id' => 1],
            ['estado' => false, 'comando_id' => null, 'sensor3' => 0]
        );

        $s1 = S1::firstOrCreate(
            ['id' => 1],
            ['estado' => false, 'comando_id' => null, 'sensor1' => 0, 'sensor2' => 0, 'valvula14' => 0]
        );

        $s2 = S2::firstOrCreate(
            ['id' => 1],
            ['estado' => 'off', 'comando_id' => null, 'valvula1' => 0, 'valvula2' => 0, 'valvula3' => 0, 'valvula4' => 0, 'valvula5' => 0, 'valvula6' => 0, 'valvula7' => 0, 'valvula8' => 0, 'valvula9' => 0, 'valvula10' => 0, 'valvula11' => 0, 'valvula12' => 0, 'valvula13' => 0]
        );

        $s3 = S3::firstOrCreate(
            ['id' => 1],
            ['estado' => false, 'comando_id' => null, 'pump1' => 0, 'pump2' => 0]
        );

        $s4 = S4::firstOrCreate(
            ['id' => 1],
            ['estado' => false, 'comando_id' => null, 'pump3' => 0, 'pump4' => 0]
        );

        $s5 = S5::firstOrCreate(
            ['id' => 1],
            ['estado' => false, 'comando_id' => null, 'flux1' => 0, 'flux2' => 0]
        );

        // Crear registro en estado_sistemas con ID 1 y que referencia a los registros iniciales
        EstadoSistema::firstOrCreate([
            'id' => 1,
            's0_id' => $s0->id,
            's1_id' => $s1->id,
            's2_id' => $s2->id,
            's3_id' => $s3->id,
            's4_id' => $s4->id,
            's5_id' => $s5->id,
        ]);
    }
}
