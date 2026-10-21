<?php

namespace Database\Factories;

use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedItem>
 */
class SavedItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shopping_list_id' => null,
            'name' => fake()->word(),
        ];
    }
}
