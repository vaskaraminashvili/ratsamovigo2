<?php

use App\Livewire\Lists\Show;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\ShoppingList;
use App\Models\User;
use App\Notifications\GoingToStoreNotification;
use App\Support\Ka;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('responses carry security headers', function () {
    $this->get(route('login'))
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy')
        ->assertHeader('Content-Security-Policy');

    expect($this->get(route('login'))->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'");
});

test('hsts is only sent over https', function () {
    $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');
    $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
});

test('adding items is rate limited per user', function () {
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    foreach (range(1, 60) as $i) {
        $component->call('addItem', "Item {$i}", null, 'ცალი', false);
    }

    $component->call('addItem', 'One too many', null, 'ცალი', false)->assertStatus(429);

    expect($this->list->items()->count())->toBe(60);
});

test('the rate limit is per user', function () {
    RateLimiter::hit('action:add-item:'.$this->user->id);
    foreach (range(1, 59) as $ignored) {
        RateLimiter::hit('action:add-item:'.$this->user->id);
    }

    $other = User::factory()->create();
    $this->list->members()->attach($other->id);
    $this->actingAs($other);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'Fine', null, 'ცალი', false)->assertHasNoErrors();
});

test('voting is rate limited', function () {
    $poll = Poll::factory()->create(['shopping_list_id' => $this->list->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    foreach (range(1, 60) as $ignored) {
        $component->call('vote', $poll->id, $option->id);
    }

    $component->call('vote', $poll->id, $option->id)->assertStatus(429);
});

test('store notifications are rate limited', function () {
    Notification::fake();
    $nino = User::factory()->create();
    $this->list->members()->attach($nino->id);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    foreach (range(1, 5) as $ignored) {
        $component->call('startShopping', [$nino->id], 'Hi', false);
    }

    $component->call('startShopping', [$nino->id], 'Hi', false)->assertStatus(429);

    Notification::assertSentToTimes($nino, GoingToStoreNotification::class, 10);
});

test('over long input is rejected', function () {
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    $component->call('addItem', str_repeat('ა', 121), null, 'ცალი', false)->assertHasErrors('name');
    $component->call('addItem', 'Milk', 100000, 'ცალი', false)->assertHasErrors('quantity');
    $component->call('createPoll', str_repeat('ა', 161), ['A', 'B'])->assertHasErrors('question');
    $component->call('createPoll', 'Q?', [str_repeat('ა', 81), 'B'])->assertHasErrors('options.0');
    $component->call('addDish', str_repeat('ა', 121), [['name' => 'Salt', 'qty' => null, 'unit' => 'ცალი']], false)->assertHasErrors('name');
});

test('georgian counts keep the noun singular', function () {
    expect(Ka::items(1))->toBe('1 ნივთი')
        ->and(Ka::items(5))->toBe('5 ნივთი')
        ->and(Ka::members(2))->toBe('2 წევრი')
        ->and(Ka::votes(0))->toBe('0 ხმა')
        ->and(Ka::ingredients(1200))->toBe('1 200 ინგრედიენტი');
});
