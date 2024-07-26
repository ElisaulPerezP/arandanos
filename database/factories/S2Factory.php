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
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'valvula1' => $this->faker->boolean,
            'valvula2' => $this->faker->boolean,
            'valvula3' => $this->faker->boolean,
            'valvula4' => $this->faker->boolean,
            'valvula5' => $this->faker->boolean,
            'valvula6' => $this->faker->boolean,
            'valvula7' => $this->faker->boolean,
            'valvula8' => $this->faker->boolean,
            'valvula9' => $this->faker->boolean,
            'valvula10' => $this->faker->boolean,
            'valvula11' => $this->faker->boolean,
            'valvula12' => $this->faker->boolean,
            'valvula13' => $this->faker->boolean,
        ];
    }
}
