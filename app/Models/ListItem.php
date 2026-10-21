<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\Unit;
use Database\Factories\ListItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shopping_list_id
 * @property int $added_by
 * @property int|null $claimed_by
 * @property string $name
 * @property string|null $group_name
 * @property float|null $quantity
 * @property Unit $unit
 * @property Priority $priority
 * @property int|null $bought_by
 * @property Carbon|null $bought_at
 */
#[Fillable(['shopping_list_id', 'added_by', 'claimed_by', 'name', 'group_name', 'quantity', 'unit', 'priority', 'bought_by', 'bought_at'])]
class ListItem extends Model
{
    /** @use HasFactory<ListItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'unit' => Unit::class,
            'priority' => Priority::class,
            'bought_at' => 'datetime',
        ];
    }

    public function isBought(): bool
    {
        return $this->bought_at !== null;
    }

    /**
     * Quantity label such as "1,5 კგ", or an empty string when no quantity is set.
     */
    public function quantityLabel(): string
    {
        if ($this->quantity === null) {
            return '';
        }

        return str_replace('.', ',', (string) $this->quantity).' '.$this->unit->value;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bought_by');
    }

    /**
     * @return BelongsTo<ShoppingList, $this>
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
