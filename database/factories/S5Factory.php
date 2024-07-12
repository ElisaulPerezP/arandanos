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
            'estado' => false,
            'comando_id' => null,
            'flux1' => 0,
            'flux2' => 0,
        ];
    }
}
