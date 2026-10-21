<?php

namespace App\Models;

use App\Enums\BoughtBehavior;
use App\Notifications\ListActivityNotification;
use App\Support\CoverImage;
use Database\Factories\ShoppingListFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property BoughtBehavior $bought_behavior
 * @property bool $group_by_priority
 * @property bool $fetch_best_price
 * @property string $invite_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['owner_id', 'name', 'bought_behavior', 'group_by_priority', 'fetch_best_price', 'invite_token', 'shopping_by', 'shopping_started_at', 'cover_path'])]
class ShoppingList extends Model
{
    /** @use HasFactory<ShoppingListFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (ShoppingList $list): void {
            CoverImage::delete($list->cover_path);
        });

        static::creating(function (ShoppingList $list): void {
            $list->invite_token ??= Str::random(32);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bought_behavior' => BoughtBehavior::class,
            'group_by_priority' => 'boolean',
            'fetch_best_price' => 'boolean',
            'shopping_started_at' => 'datetime',
        ];
    }

    /**
     * Tell the other members about something that just happened. Members who turned
     * activity notices off are skipped; the actor never notifies themselves.
     */
    public function notifyMembers(User $actor, string $message): void
    {
        $recipients = $this->members()
            ->where('users.id', '!=', $actor->id)
            ->where('users.notify_activity', true)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ListActivityNotification($this, $actor, $message));
        }
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path === null ? null : Storage::disk(CoverImage::DISK)->url($this->cover_path);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function shopper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shopping_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<Poll, $this>
     */
    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    /**
     * @return HasMany<ListItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ListItem::class);
    }
}
