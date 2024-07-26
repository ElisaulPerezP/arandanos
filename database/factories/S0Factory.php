<?php

namespace Database\Factories;

use App\Models\S0;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class S0Factory extends Factory
{
    protected $model = S0::class;

    public function definition()
    {
        return [
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'sensor3' => $this->faker->boolean,
        ];
    }
}
