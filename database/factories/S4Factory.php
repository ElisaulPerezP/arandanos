<?php

namespace Database\Factories;

use App\Models\S4;
use Illuminate\Database\Eloquent\Factories\Factory;

class S4Factory extends Factory
{
    protected $model = S4::class;

    public function definition()
    {
        return [
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'pump3' => $this->faker->boolean,
            'pump4' => $this->faker->boolean,
        ];
    }
}
