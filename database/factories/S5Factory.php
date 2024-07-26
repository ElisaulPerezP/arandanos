<?php

namespace Database\Factories;

use App\Models\S5;
use Illuminate\Database\Eloquent\Factories\Factory;

class S5Factory extends Factory
{
    protected $model = S5::class;

    public function definition()
    {
        return [
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'flux1' => $this->faker->randomNumber(),
            'flux2' => $this->faker->randomNumber(),
        ];
    }
}
