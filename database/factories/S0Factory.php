<?php

namespace Database\Factories;

use App\Models\S0;
use Illuminate\Database\Eloquent\Factories\Factory;

class S0Factory extends Factory
{
    protected $model = S0::class;

    public function definition()
    {
        return [
            'estado' => false,
            'comando_id' => null,
            'sensor3' => false,
        ];
    }
}
