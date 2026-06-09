<?php

namespace Database\Seeders;

use App\Models\MaintenanceLog;
use App\Models\Ship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaintenanceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 500 dummy maintenance logs distributed across all ships.
     */
    public function run(): void
    {
        $ships    = Ship::all();
        $total    = 500;
        $perShip  = (int) ceil($total / $ships->count());

        $created = 0;
        foreach ($ships as $ship) {
            $count   = min($perShip, $total - $created);
            if ($count <= 0) break;

            MaintenanceLog::factory()
                ->count($count)
                ->create(['ship_id' => $ship->id]);

            $created += $count;
        }

        $this->command->info("✓ Created {$created} maintenance logs successfully.");
    }
}
