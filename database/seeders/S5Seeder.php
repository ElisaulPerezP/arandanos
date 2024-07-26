<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S5;

class S5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S5::factory()->count(1)->create();
    }
}
