<?php

namespace Database\Factories;

use App\Models\S3;
use Illuminate\Database\Eloquent\Factories\Factory;

class S3Factory extends Factory
{
    protected $model = S3::class;

    public function definition()
    {
        return [
            'estado' => $this->faker->boolean,
            'comando_id' => null,  // Permitir que sea nulo
            'pump1' => $this->faker->boolean,
            'pump2' => $this->faker->boolean,
        ];
    }
}
