<?php

namespace App\Models;

use Database\Factories\SavedItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $shopping_list_id
 * @property string $name
 * @property string $name_key
 * @property float|null $quantity
 * @property string|null $unit
 */
#[Fillable(['user_id', 'shopping_list_id', 'name', 'name_key', 'quantity', 'unit'])]
class SavedItem extends Model
{
    /** @use HasFactory<SavedItemFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (SavedItem $saved): void {
            $saved->name_key = static::keyFor($saved->name);
        });
    }

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
     * Normalized form used to match the same item across lists (case and spacing insensitive).
     */
    public static function keyFor(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $name)));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ShoppingList, $this>
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
