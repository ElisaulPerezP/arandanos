<?php

namespace Database\Factories;

use App\Models\EstadoSistema;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstadoSistemaFactory extends Factory
{
    protected $model = EstadoSistema::class;

    public function definition()
    {
        return [
            's0_id' => 1,
            's1_id' => 1,
            's2_id' => 1,
            's3_id' => 1,
            's4_id' => 1,
            's5_id' => 1,
        ];
    }
}
