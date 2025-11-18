<?php

namespace Database\Factories;

use App\Models\ChecklistVersion;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistResponse>
 */
class ChecklistResponseFactory extends Factory
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
            'staff_id' => Staff::factory(),
            'response_date' => fake()->date(),
            'completed_at' => fake()->optional()->dateTime(),
        ];
    }
}
