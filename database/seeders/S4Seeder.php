<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S4;

class S4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S4::factory()->count(1)->create();
    }
}
