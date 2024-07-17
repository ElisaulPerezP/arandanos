<?php

namespace Database\Factories;

use App\Models\S2;
use Illuminate\Database\Eloquent\Factories\Factory;

class S2Factory extends Factory
{
    protected $model = S2::class;

    public function definition()
    {
        return [
            'estado' => false,
            'comando_id' => null,
            'valvula1' => false,
            'valvula2' => false,
            'valvula3' => false,
            'valvula4' => false,
            'valvula5' => false,
            'valvula6' => false,
            'valvula7' => false,
            'valvula8' => false,
            'valvula9' => false,
            'valvula10' => false,
            'valvula11' => false,
            'valvula12' => false,
            'valvula13' => false,
        ];
    }
}
