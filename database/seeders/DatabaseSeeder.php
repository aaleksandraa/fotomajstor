<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            PhotographerSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
