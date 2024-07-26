<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S0;

class S0Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S0::factory()->count(1)->create();
    }
}

