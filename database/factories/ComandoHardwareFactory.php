<?php

namespace Database\Factories;

use App\Models\ComandoHardware;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComandoHardwareFactory extends Factory
{
    protected $model = ComandoHardware::class;

    public function definition()
    {
        return [
            'sistema' => '',
            'comando' => '',
        ];
    }
}
