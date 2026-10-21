<?php

namespace App\Models;

use Database\Factories\DishIngredientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dish_id
 * @property string $name
 * @property float|null $quantity
 * @property string|null $unit
 */
#[Fillable(['dish_id', 'name', 'quantity', 'unit'])]
class DishIngredient extends Model
{
    /** @use HasFactory<DishIngredientFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
