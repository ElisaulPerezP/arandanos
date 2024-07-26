<?php

namespace Database\Factories;

use App\Models\S1;
use Illuminate\Database\Eloquent\Factories\Factory;

class S1Factory extends Factory
{
    protected $model = S1::class;

    public function definition()
    {
        return [
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'sensor1' => $this->faker->boolean,
            'sensor2' => $this->faker->boolean,
            'valvula14' => $this->faker->boolean,
        ];
    }
}
