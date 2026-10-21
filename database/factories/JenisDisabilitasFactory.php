<?php

namespace Database\Factories;

use App\Models\JenisDisabilitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisDisabilitas>
 */
class JenisDisabilitasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['Tuna Wicara', 'Tuna Rungu', 'Tuna Netra']),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    public function tunaWicara(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => 'Tuna Wicara',
        ]);
    }

    public function tunaRungu(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => 'Tuna Rungu',
        ]);
    }

    public function tunaNetra(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => 'Tuna Netra',
        ]);
    }
}
