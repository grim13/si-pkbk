<?php

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['Pijat Shiatsu', 'Orientasi & Mobilitas', 'Bahasa Isyarat', 'Menjahit', 'Komputer Bicara']),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
