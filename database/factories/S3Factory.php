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
            'estado' => false,
            'comando_id' => null,
            'pump1' => false,
            'pump2' => false,
        ];
    }
}
