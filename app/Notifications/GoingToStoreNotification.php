<?php

namespace App\Notifications;

use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoingToStoreNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShoppingList $shoppingList,
        public User $sender,
        public string $message,
    ) {}

    /**
     * Queued delivery is email only. The in-app copy is stored right away with
     * Notification::sendNow(..., ['database']) so it never waits for a queue worker.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->sender->name} მაღაზიაში მიდის")
            ->greeting("სია: {$this->shoppingList->name}")
            ->line($this->message)
            ->action('სიის გახსნა', route('lists.show', $this->shoppingList));
    }

    /**
     * @return array{list_id: int, list_name: string, from_id: int, from_name: string, message: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'list_id' => $this->shoppingList->id,
            'list_name' => $this->shoppingList->name,
            'from_id' => $this->sender->id,
            'from_name' => $this->sender->name,
            'message' => $this->message,
        ];
    }
}
