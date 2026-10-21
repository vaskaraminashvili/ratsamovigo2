<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poll>
 */
class PollFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shopping_list_id' => ShoppingList::factory(),
            'created_by' => User::factory(),
            'question' => fake()->sentence(3).'?',
        ];
    }
}
