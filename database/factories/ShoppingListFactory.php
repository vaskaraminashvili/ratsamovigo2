<?php

namespace Database\Factories;

use App\Enums\BoughtBehavior;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShoppingList>
 */
class ShoppingListFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->words(2, true),
            'bought_behavior' => BoughtBehavior::Strike,
        ];
    }

    public function daily(): static
    {
        return $this->state(['bought_behavior' => BoughtBehavior::Hide]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ShoppingList $list): void {
            $list->members()->syncWithoutDetaching([$list->owner_id]);
        });
    }
}
