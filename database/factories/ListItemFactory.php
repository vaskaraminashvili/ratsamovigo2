<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\Unit;
use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListItem>
 */
class ListItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shopping_list_id' => ShoppingList::factory(),
            'added_by' => User::factory(),
            'name' => fake()->word(),
            'quantity' => null,
            'unit' => Unit::Piece,
            'priority' => Priority::Normal,
            'bought_at' => null,
        ];
    }

    public function urgent(): static
    {
        return $this->state(['priority' => Priority::Urgent]);
    }

    public function bought(): static
    {
        return $this->state(['bought_at' => now()]);
    }
}
