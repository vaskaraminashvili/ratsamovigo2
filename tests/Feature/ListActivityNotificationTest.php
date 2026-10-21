<?php

use App\Livewire\Lists\Index;
use App\Livewire\Lists\Show;
use App\Livewire\Settings\Profile;
use App\Models\ShoppingList;
use App\Models\User;
use App\Notifications\ListActivityNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();

    $this->owner = User::factory()->create(['name' => 'Dato']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->owner->id, 'name' => 'Home']);
    $this->nino = User::factory()->create(['name' => 'Nino']);
    $this->list->members()->attach($this->nino->id);
});

test('an urgent item notifies the other members but not the author', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Milk', null, 'ცალი', true);

    Notification::assertSentTo($this->nino, ListActivityNotification::class, fn ($n, $channels) => $channels === ['database'] && str_contains($n->message, 'Milk'));
    Notification::assertNotSentTo($this->owner, ListActivityNotification::class);
});

test('a normal item sends nothing', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Bread', null, 'ცალი', false);

    Notification::assertNothingSent();
});

test('a new poll notifies the other members', function () {
    $this->actingAs($this->nino);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('createPoll', 'Dinner?', ['Pizza', 'Soup']);

    Notification::assertSentTo($this->owner, ListActivityNotification::class, fn ($n) => str_contains($n->message, 'Dinner?'));
    Notification::assertNotSentTo($this->nino, ListActivityNotification::class);
});

test('joining through the invite page notifies existing members once', function () {
    $joiner = User::factory()->create(['name' => 'Gio']);

    $this->actingAs($joiner)->post(route('join.store', $this->list->invite_token));
    $this->post(route('join.store', $this->list->invite_token));

    Notification::assertSentToTimes($this->owner, ListActivityNotification::class, 1);
    Notification::assertSentToTimes($this->nino, ListActivityNotification::class, 1);
    Notification::assertNotSentTo($joiner, ListActivityNotification::class);
});

test('members who opted out are skipped', function () {
    $this->nino->update(['notify_activity' => false]);
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Milk', null, 'ცალი', true);

    Notification::assertNothingSent();
});

test('the opt out can be changed on the profile', function () {
    $this->actingAs($this->nino);

    Livewire::test(Profile::class)->assertSet('notifyActivity', true)->set('notifyActivity', false);
    expect($this->nino->fresh()->notify_activity)->toBeFalse();

    Livewire::test(Profile::class)->assertSet('notifyActivity', false)->set('notifyActivity', true);
    expect($this->nino->fresh()->notify_activity)->toBeTrue();
});

test('stored activity notices show in the inbox and clear when the list is opened', function () {
    $notification = new ListActivityNotification($this->list, $this->nino, 'Nino შეუერთდა სიას');

    $this->owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => $notification::class,
        'data' => $notification->toArray($this->owner),
    ]);

    $this->actingAs($this->owner);
    Livewire::test(Index::class)->assertSee('Nino შეუერთდა სიას')->assertSee('Home');

    $this->get(route('lists.show', $this->list))->assertOk();

    expect($this->owner->unreadNotifications()->count())->toBe(0);
});
