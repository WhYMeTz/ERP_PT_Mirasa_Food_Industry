<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UpdateCoaBaruSeeder::class);
    }
}
