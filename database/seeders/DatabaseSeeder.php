<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            EstadoSeeder::class,
            ComandoSeeder::class,
            ComandoHardwareSeeder::class,
            S0Seeder::class,
            S1Seeder::class,
            S2Seeder::class,
            S3Seeder::class,
            S4Seeder::class,
            S5Seeder::class,
            EstadoSistemasSeeder::class,
        ]);
    }
}
