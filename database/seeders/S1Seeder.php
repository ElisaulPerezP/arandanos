<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S1;

class S1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S1::factory()->count(1)->create();
    }
}
