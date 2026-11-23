<?php

namespace App\Notifications;

use App\Models\ShoppingList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Device push for the same events as the in-app notices. Queued, so the request
 * that triggered it never waits on the push services. Needs a queue worker.
 */
class ListPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShoppingList $shoppingList,
        public string $title,
        public string $body,
    ) {}

    /**
     * @return list<class-string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon('/icons/icon-192.png')
            ->tag('list-'.$this->shoppingList->id)
            ->data(['url' => route('lists.show', $this->shoppingList)]);
    }
}
