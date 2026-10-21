<?php

namespace App\Notifications;

use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * In-app notice about something that happened on a shared list. Stored immediately
 * (no queue) in the same shape as the store-trip notice so the inbox renders both.
 */
class ListActivityNotification extends Notification
{
    public function __construct(
        public ShoppingList $shoppingList,
        public User $actor,
        public string $message,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{list_id: int, list_name: string, from_id: int, from_name: string, message: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'list_id' => $this->shoppingList->id,
            'list_name' => $this->shoppingList->name,
            'from_id' => $this->actor->id,
            'from_name' => $this->actor->name,
            'message' => $this->message,
        ];
    }
}
