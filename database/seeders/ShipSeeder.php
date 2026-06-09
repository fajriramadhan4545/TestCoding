<?php

namespace Database\Seeders;

use App\Models\Ship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 50 dummy ships.
     */
    public function run(): void
    {
        Ship::factory()->count(50)->create();

        $this->command->info('✓ Created 50 ships successfully.');
    }
}
