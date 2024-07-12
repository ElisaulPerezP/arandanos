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
            'estado' => false,
            'comando_id' => null,
            'pump3' => false,
            'pump4' => false,
        ];
    }
}
