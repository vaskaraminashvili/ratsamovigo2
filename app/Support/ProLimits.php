<?php

namespace App\Support;

use App\Models\ShoppingList;
use App\Models\User;

/**
 * What the free tier may do. Pro users are never limited.
 */
class ProLimits
{
    public const FREE_MAX_MEMBERS = 2;

    /** @var list<string> */
    public const FREE_THEMES = ['system', 'night'];

    public const LOCKED_MESSAGE = 'ეს Pro ვერსიაშია';

    public const ALREADY_IN_LIST = 'უფასო ვერსიაში მხოლოდ ერთ სიაში შეგიძლია იყო. მეტისთვის საჭიროა Pro.';

    public const LIST_FULL = 'ეს სია სავსეა: უფასო ვერსიაში სიაზე მაქსიმუმ 2 წევრია.';

    public static function canHaveAnotherList(User $user): bool
    {
        return $user->isPro() || ! $user->shoppingLists()->exists();
    }

    public static function themeAllowed(User $user, string $theme): bool
    {
        return $user->isPro() || in_array($theme, self::FREE_THEMES, true);
    }

    /**
     * Why the user cannot join the list, or null when they can (including when they already are a member).
     */
    public static function joinBlocker(User $user, ShoppingList $list): ?string
    {
        if ($list->members()->whereKey($user->id)->exists()) {
            return null;
        }

        if (! self::canHaveAnotherList($user)) {
            return self::ALREADY_IN_LIST;
        }

        if (! $list->owner->isPro() && $list->members()->count() >= self::FREE_MAX_MEMBERS) {
            return self::LIST_FULL;
        }

        return null;
    }
}
