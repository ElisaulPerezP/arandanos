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
            'estado' => false,
            'comando_id' => null,
            'sensor1' => false,
            'sensor2' => false,
            'valvula14' => false,
        ];
    }
}
