<?php

namespace Database\Factories;

use App\Models\ChecklistVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checklist_version_id' => ChecklistVersion::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 100),
            'scoring_type' => fake()->randomElement(['binary', 'numeric', 'text']),
        ];
    }
}
