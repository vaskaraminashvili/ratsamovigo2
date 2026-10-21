<?php

namespace App\Livewire\Concerns;

use App\Enums\Unit;
use Illuminate\Validation\Rule;

trait ValidatesIngredients
{
    /**
     * Validate ingredient rows sent from the dish form and normalize them.
     *
     * @param  array<int, mixed>  $rows
     * @return list<array{name: string, quantity: float|null, unit: string}>
     */
    protected function validatedIngredients(array $rows): array
    {
        $rows = array_values(array_filter(
            array_slice(array_values($rows), 0, 40),
            fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== '',
        ));

        $rows = array_map(fn (array $row) => [
            'name' => trim((string) $row['name']),
            'qty' => $row['qty'] ?? null,
            'unit' => (string) ($row['unit'] ?? Unit::Piece->value),
        ], $rows);

        $validated = validator(['ingredients' => $rows], [
            'ingredients' => ['required', 'array', 'min:1', 'max:40'],
            'ingredients.*.name' => ['required', 'string', 'max:120'],
            'ingredients.*.qty' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
            'ingredients.*.unit' => [Rule::enum(Unit::class)],
        ], [
            'ingredients.required' => 'დაამატე მინიმუმ ერთი ინგრედიენტი.',
            'ingredients.min' => 'დაამატე მინიმუმ ერთი ინგრედიენტი.',
        ])->validate();

        $ingredients = [];

        foreach ($validated['ingredients'] as $row) {
            $ingredients[] = [
                'name' => (string) $row['name'],
                'quantity' => $row['qty'] === null ? null : (float) $row['qty'],
                'unit' => (string) $row['unit'],
            ];
        }

        return $ingredients;
    }
}
