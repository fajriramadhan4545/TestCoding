<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ship>
 */
class ShipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shipTypes = [
            'MV', 'MT', 'KM', 'KMC', 'TB', 'LCT', 'SPOB', 'TK', 'BG', 'KMP'
        ];

        $shipNames = [
            'Nusantara', 'Bahari', 'Samudera', 'Laut Biru', 'Jaya',
            'Maju', 'Sejahtera', 'Makmur', 'Persada', 'Pertiwi',
            'Cahaya', 'Sinar', 'Bintang', 'Mulia', 'Abadi',
            'Harapan', 'Karya', 'Agung', 'Mandiri', 'Utama',
            'Setia', 'Sakti', 'Perkasa', 'Gemilang', 'Sentosa',
        ];

        $type    = $this->faker->randomElement($shipTypes);
        $name    = $this->faker->randomElement($shipNames);
        $number  = $this->faker->numberBetween(1, 999);
        $year    = $this->faker->numberBetween(1990, 2023);
        $code    = strtoupper($type) . '-' . str_pad($number, 4, '0', STR_PAD_LEFT) . '-' . $year;

        return [
            'nama'            => $type . ' ' . $name . ' ' . $this->faker->randomLetter() . $number,
            'kode_kapal'      => $code . '-' . $this->faker->unique()->numerify('##'),
            'tahun_pembuatan' => $year,
        ];
    }
}
