<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => 'recipe.ge',
            'url' => fake()->unique()->url(),
            'title' => fake()->unique()->words(2, true),
            'categories' => ['სალათი'],
            'parts' => [['name' => null, 'ingredients' => ['ხახვი — 500 გრ (წვრილად დაკეპილი)', '2 თავი ნიორი']]],
            'preparation' => ['მოამზადეთ.'],
        ];
    }
}
