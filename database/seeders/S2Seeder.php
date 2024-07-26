<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S2;

class S2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S2::factory()->count(1)->create();
    }
}
