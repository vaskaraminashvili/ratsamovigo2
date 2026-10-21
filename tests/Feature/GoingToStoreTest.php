<?php

use App\Livewire\Lists\Index;
use App\Livewire\Lists\Show;
use App\Models\ShoppingList;
use App\Models\User;
use App\Notifications\GoingToStoreNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();

    $this->user = User::factory()->create(['name' => 'Dato']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id, 'name' => 'Home']);
    $this->nino = User::factory()->create(['name' => 'Nino']);
    $this->gio = User::factory()->create(['name' => 'Gio']);
    $this->list->members()->attach([$this->nino->id, $this->gio->id]);
    $this->actingAs($this->user);
});

test('chosen members are notified and the trip starts', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$this->nino->id], '  Going now ', false)
        ->assertHasNoErrors();

    Notification::assertSentTo($this->nino, GoingToStoreNotification::class, fn ($n, $channels) => $channels === ['database'] && $n->message === 'Going now');
    Notification::assertSentTo($this->nino, GoingToStoreNotification::class, fn ($n, $channels) => $channels === ['mail']);
    Notification::assertNotSentTo($this->gio, GoingToStoreNotification::class);

    $this->list->refresh();
    expect($this->list->shopping_by)->toBe($this->user->id)->and($this->list->shopping_started_at)->not->toBeNull();
});

test('the sender is never a recipient', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$this->user->id, $this->gio->id], 'Hi', false);

    Notification::assertNotSentTo($this->user, GoingToStoreNotification::class);
    Notification::assertSentTo($this->gio, GoingToStoreNotification::class);
});

test('only the sender in the recipient list is rejected', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$this->user->id], 'Hi', false)
        ->assertHasErrors('recipients');

    Notification::assertNothingSent();
});

test('non members cannot be notified', function () {
    $outsider = User::factory()->create();

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$outsider->id, $this->nino->id], 'Hi', false)
        ->assertHasErrors('recipients');

    Notification::assertNothingSent();
    expect($this->list->fresh()->shopping_by)->toBeNull();
});

test('the message is required and limited to 160 characters', function () {
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    $component->call('startShopping', [$this->nino->id], '   ', false)->assertHasErrors('message');
    $component->call('startShopping', [$this->nino->id], str_repeat('ა', 161), false)->assertHasErrors('message');
    $component->call('startShopping', [$this->nino->id], str_repeat('ა', 160), false)->assertHasNoErrors();
});

test('recipients are required', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [], 'Hi', false)
        ->assertHasErrors('recipients');
});

test('the message can be remembered', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$this->nino->id], 'Usual text', true);

    expect($this->user->fresh()->going_message)->toBe('Usual text');

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('finishShopping');
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('startShopping', [$this->nino->id], 'Other text', false);

    expect($this->user->fresh()->going_message)->toBe('Usual text');
});

test('the shopper can end the trip', function () {
    $this->list->update(['shopping_by' => $this->user->id, 'shopping_started_at' => now()]);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('finishShopping');

    expect($this->list->fresh()->shopping_by)->toBeNull();
});

test('another member cannot end someone elses trip but the owner can', function () {
    $this->list->update(['shopping_by' => $this->nino->id, 'shopping_started_at' => now()]);

    $this->actingAs($this->gio);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('finishShopping')->assertForbidden();
    expect($this->list->fresh()->shopping_by)->toBe($this->nino->id);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('finishShopping');
    expect($this->list->fresh()->shopping_by)->toBeNull();
});

test('non members cannot start a trip', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertForbidden();
});

test('the banner reflects the trip state', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSee('შეატყობინე სხვებს, სანამ გახვალ.');

    $this->list->update(['shopping_by' => $this->nino->id, 'shopping_started_at' => now()]);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSee('Nino მაღაზიაშია')->assertDontSee('შეატყობინე სხვებს, სანამ გახვალ.');
});

test('a solo list has no banner', function () {
    $solo = ShoppingList::factory()->create(['owner_id' => $this->user->id]);

    Livewire::test(Show::class, ['shoppingList' => $solo])->assertDontSee('შეატყობინე სხვებს, სანამ გახვალ.');
});

test('the in-app inbox shows and clears notifications', function () {
    $this->nino->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => GoingToStoreNotification::class,
        'data' => ['list_id' => $this->list->id, 'list_name' => 'Home', 'from_id' => $this->user->id, 'from_name' => 'Dato', 'message' => 'On my way'],
    ]);

    $this->actingAs($this->nino);
    $component = Livewire::test(Index::class)->assertSee('On my way');

    $component->call('markNotificationsRead')->assertDontSee('On my way');
    expect($this->nino->unreadNotifications()->count())->toBe(0);
});

test('opening the list marks its notifications as read', function () {
    $this->nino->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => GoingToStoreNotification::class,
        'data' => ['list_id' => $this->list->id, 'list_name' => 'Home', 'from_id' => $this->user->id, 'from_name' => 'Dato', 'message' => 'On my way'],
    ]);

    $this->actingAs($this->nino)->get(route('lists.show', $this->list))->assertOk();

    expect($this->nino->unreadNotifications()->count())->toBe(0);
});
