<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\S3;

class S3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S3::factory()->count(1)->create();
    }
}
