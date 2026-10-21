<?php

namespace App\Livewire\Concerns;

use App\Models\ShoppingList;
use App\Models\User;

trait JoinsPendingInvite
{
    /**
     * Join the list from an invite link the guest opened before logging in or registering.
     */
    protected function joinPendingInvite(User $user): void
    {
        $token = session()->pull('invite_token');

        if (! $token) {
            return;
        }

        $list = ShoppingList::query()->where('invite_token', $token)->first();

        if ($list === null) {
            return;
        }

        $changes = $list->members()->syncWithoutDetaching([$user->id]);

        if ($changes['attached'] !== []) {
            $list->notifyMembers($user, "{$user->name} შეუერთდა სიას");
        }
    }
}
