<?php

namespace App\Models;

use App\Support\CoverImage;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property bool $is_pro
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'theme', 'going_message', 'notify_activity', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'notify_activity' => true,
        'is_pro' => false,
    ];

    protected static function booted(): void
    {
        static::deleted(fn (User $user) => CoverImage::delete($user->avatar_path));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_activity' => 'boolean',
            'is_pro' => 'boolean',
        ];
    }

    public function isPro(): bool
    {
        return $this->is_pro;
    }

    /**
     * @return BelongsToMany<ShoppingList, $this>
     */
    public function shoppingLists(): BelongsToMany
    {
        return $this->belongsToMany(ShoppingList::class)->withPivot('position')->withTimestamps();
    }

    /**
     * @return HasMany<SavedItem, $this>
     */
    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class);
    }

    /**
     * @return HasMany<Dish, $this>
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    /**
     * Saved items, cached until the user's saved items or dishes change.
     * Only raw attribute arrays are cached: cache.serializable_classes forbids storing models.
     *
     * @return Collection<int, SavedItem>
     */
    public function cachedSavedItems(): Collection
    {
        $rows = Cache::rememberForever(
            "user:{$this->id}:saved-items",
            fn () => $this->savedItems()->orderBy('name')->get()->map->getAttributes()->all(),
        );

        return SavedItem::hydrate($rows);
    }

    /**
     * Dishes with ingredients, cached until the user's dishes change.
     *
     * @return Collection<int, Dish>
     */
    public function cachedDishes(): Collection
    {
        $rows = Cache::rememberForever(
            "user:{$this->id}:dishes",
            fn () => $this->dishes()->with('ingredients')->orderBy('name')->get()->map(fn (Dish $dish) => [
                'attributes' => $dish->getAttributes(),
                'ingredients' => $dish->ingredients->map->getAttributes()->all(),
            ])->all(),
        );

        return Dish::hydrate(array_column($rows, 'attributes'))
            ->each(fn (Dish $dish, int $index) => $dish->setRelation('ingredients', DishIngredient::hydrate($rows[$index]['ingredients'])));
    }

    public static function forgetCatalogCache(?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        Cache::forget("user:{$userId}:saved-items");
        Cache::forget("user:{$userId}:dishes");
    }

    /**
     * Stable avatar colour class so a person keeps their colour in every list.
     */
    public function avatarColorClass(): string
    {
        return ['bg-m1', 'bg-m2', 'bg-m3', 'bg-lilac'][$this->id % 4];
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path === null ? null : Storage::disk(CoverImage::DISK)->url($this->avatar_path);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
