<?php

namespace App\Policies;

use App\Models\ShoppingList;
use App\Models\User;

class ShoppingListPolicy
{
    /**
     * Any member can see and edit items on a list.
     */
    public function view(User $user, ShoppingList $shoppingList): bool
    {
        return $shoppingList->members()->whereKey($user->id)->exists();
    }

    /**
     * Only the owner can change list settings or delete it.
     */
    public function update(User $user, ShoppingList $shoppingList): bool
    {
        return $shoppingList->owner_id === $user->id;
    }
}
