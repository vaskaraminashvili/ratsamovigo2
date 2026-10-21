<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\DishIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DishIngredient>
 */
class DishIngredientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dish_id' => Dish::factory(),
            'name' => fake()->word(),
            'quantity' => null,
            'unit' => null,
        ];
    }
}
