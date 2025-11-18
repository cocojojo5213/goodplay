<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use App\Models\ChecklistResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistResponseItem>
 */
class ChecklistResponseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checklist_response_id' => ChecklistResponse::factory(),
            'checklist_item_id' => ChecklistItem::factory(),
            'score' => fake()->numberBetween(0, 100),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
