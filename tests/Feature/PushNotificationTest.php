<?php

use App\Livewire\Lists\Show;
use App\Models\ShoppingList;
use App\Models\User;
use App\Notifications\ListPushNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();

    $this->owner = User::factory()->create(['name' => 'Dato']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->owner->id, 'name' => 'Home']);
    $this->nino = User::factory()->create(['name' => 'Nino']);
    $this->list->members()->attach($this->nino->id);
});

/**
 * @return array{endpoint: string, keys: array{p256dh: string, auth: string}}
 */
function pushSubscriptionPayload(string $endpoint = 'https://push.example/device-a'): array
{
    return ['endpoint' => $endpoint, 'keys' => ['p256dh' => 'p256-key', 'auth' => 'auth-secret']];
}

test('a signed-in user can save a device subscription', function () {
    $this->actingAs($this->nino)
        ->postJson(route('push-subscriptions.store'), pushSubscriptionPayload())
        ->assertCreated()
        ->assertJson(['subscribed' => true]);

    $this->assertDatabaseHas('push_subscriptions', [
        'subscribable_id' => $this->nino->id,
        'endpoint' => 'https://push.example/device-a',
        'public_key' => 'p256-key',
        'auth_token' => 'auth-secret',
    ]);
});

test('saving the same device twice keeps one subscription', function () {
    $this->actingAs($this->nino);

    $this->postJson(route('push-subscriptions.store'), pushSubscriptionPayload());
    $this->postJson(route('push-subscriptions.store'), pushSubscriptionPayload());

    expect($this->nino->pushSubscriptions()->count())->toBe(1);
});

test('a guest cannot save a subscription', function () {
    $this->postJson(route('push-subscriptions.store'), pushSubscriptionPayload())
        ->assertUnauthorized();

    $this->assertDatabaseCount('push_subscriptions', 0);
});

test('a subscription without keys is rejected', function () {
    $this->actingAs($this->nino)
        ->postJson(route('push-subscriptions.store'), ['endpoint' => 'https://push.example/device-a'])
        ->assertUnprocessable();

    $this->assertDatabaseCount('push_subscriptions', 0);
});

test('unsubscribing removes only that device', function () {
    $this->actingAs($this->nino);

    $this->postJson(route('push-subscriptions.store'), pushSubscriptionPayload('https://push.example/device-a'));
    $this->postJson(route('push-subscriptions.store'), pushSubscriptionPayload('https://push.example/device-b'));

    $this->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => 'https://push.example/device-a'])
        ->assertOk()
        ->assertJson(['subscribed' => false]);

    expect($this->nino->pushSubscriptions()->pluck('endpoint')->all())->toBe(['https://push.example/device-b']);
});

test('an urgent item pushes to other members who have a device subscribed', function () {
    $this->nino->pushSubscriptions()->create(['endpoint' => 'https://push.example/nino', 'public_key' => 'k', 'auth_token' => 't']);

    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Milk', null, 'ცალი', true);

    Notification::assertSentTo(
        $this->nino,
        ListPushNotification::class,
        fn (ListPushNotification $notification) => $notification->title === 'Home' && $notification->body !== '' && str_contains($notification->body, 'Milk'),
    );
    Notification::assertNotSentTo($this->owner, ListPushNotification::class);
});

test('members without a device subscription get no push', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Milk', null, 'ცალი', true);

    Notification::assertNotSentTo($this->nino, ListPushNotification::class);
});

test('the push payload opens the list it came from', function () {
    $notification = new ListPushNotification($this->list, 'Home', 'Milk');

    $message = $notification->toWebPush($this->nino, $notification)->toArray();

    expect($message['title'])->toBe('Home')
        ->and($message['body'])->toBe('Milk')
        ->and($message['data']['url'])->toBe(route('lists.show', $this->list));
});

test('the service worker shows pushes and opens the list on tap', function () {
    $worker = file_get_contents(public_path('sw.js'));

    expect($worker)->toContain("addEventListener('push'")
        ->and($worker)->toContain("addEventListener('notificationclick'");
});

test('the settings page offers device notifications', function () {
    $this->actingAs($this->nino)
        ->get(route('settings.profile'))
        ->assertOk()
        ->assertSee('ეს მოწყობილობა', false);
});
