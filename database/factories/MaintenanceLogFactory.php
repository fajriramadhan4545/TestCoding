<?php

namespace Database\Factories;

use App\Models\Ship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceLog>
 */
class MaintenanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenisServis = [
            'Perawatan Mesin Utama',
            'Perawatan Mesin Bantu',
            'Pengecatan Lambung Kapal',
            'Perbaikan Sistem Kemudi',
            'Perawatan Sistem Navigasi',
            'Pemeriksaan Badan Kapal',
            'Perbaikan Pompa Bilga',
            'Perawatan Generator',
            'Pemeriksaan Sistem Listrik',
            'Perawatan Baling-baling',
            'Perbaikan Sistem Pendingin',
            'Pemeriksaan Sertifikat Kelaikan',
            'Docking (Pengedokan)',
            'Perawatan Sistem Pemadam Kebakaran',
            'Pemeriksaan Peralatan Keselamatan',
        ];

        return [
            'ship_id'      => Ship::factory(),
            'tanggal_servis' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'jenis_servis' => $this->faker->randomElement($jenisServis),
            'biaya'        => $this->faker->randomFloat(2, 5000000, 500000000),
            'status'       => $this->faker->randomElement(['planned', 'ongoing', 'completed']),
        ];
    }

    /**
     * Indicate that the maintenance log is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the maintenance log is planned.
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
        ]);
    }
}
