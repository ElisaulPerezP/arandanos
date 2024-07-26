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
        // Crear registros en s0 a s5 si no existen
        $s0 = S0::first() ?? S0::factory()->create();
        $s1 = S1::first() ?? S1::factory()->create();
        $s2 = S2::first() ?? S2::factory()->create();
        $s3 = S3::first() ?? S3::factory()->create();
        $s4 = S4::first() ?? S4::factory()->create();
        $s5 = S5::first() ?? S5::factory()->create();

        // Crear registro en estado_sistemas
        EstadoSistema::create([
            's0_id' => $s0->id,
            's1_id' => $s1->id,
            's2_id' => $s2->id,
            's3_id' => $s3->id,
            's4_id' => $s4->id,
            's5_id' => $s5->id,
        ]);
    }
}
