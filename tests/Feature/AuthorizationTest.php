<?php

use App\Livewire\Lists\Show;
use App\Models\ListItem;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

/**
 * Every action a member can trigger on a list, with plausible arguments.
 * A page can stay open after membership is revoked, so each action has to re-check access.
 *
 * @return array<string, array{0: string, 1: callable(ShoppingList): list<mixed>}>
 */
function memberActions(): array
{
    $item = fn (ShoppingList $list) => ListItem::factory()->create(['shopping_list_id' => $list->id])->id;
    $poll = function (ShoppingList $list) {
        $poll = Poll::factory()->create(['shopping_list_id' => $list->id]);
        PollOption::factory()->create(['poll_id' => $poll->id]);

        return $poll;
    };

    return [
        'addItem' => ['addItem', fn () => ['Milk', null, 'ცალი', false]],
        'saveItem' => ['saveItem', fn ($list) => [$item($list), 'Milk', null, 'ცალი', 'normal']],
        'toggleBought' => ['toggleBought', fn ($list) => [$item($list)]],
        'toggleClaim' => ['toggleClaim', fn ($list) => [$item($list)]],
        'deleteItem' => ['deleteItem', fn ($list) => [$item($list)]],
        'clearBought' => ['clearBought', fn () => []],
        'reopenBought' => ['reopenBought', fn () => []],
        'addSavedItems' => ['addSavedItems', fn () => [[]]],
        'addDish' => ['addDish', fn () => ['Soup', [['name' => 'Salt', 'qty' => null, 'unit' => 'ცალი']], false]],
        'toggleGroup' => ['toggleGroup', fn () => ['Soup']],
        'deleteGroup' => ['deleteGroup', fn () => ['Soup']],
        'createPoll' => ['createPoll', fn () => ['Q?', ['A', 'B']]],
        'vote' => ['vote', function ($list) use ($poll) {
            $created = $poll($list);

            return [$created->id, $created->options()->first()->id];
        }],
        'deletePoll' => ['deletePoll', fn ($list) => [$poll($list)->id]],
        'promotePoll' => ['promotePoll', function ($list) use ($poll) {
            $created = $poll($list);

            return [$created->id, $created->options()->first()->id];
        }],
        'startShopping' => ['startShopping', fn () => [[1], 'Hi', false]],
        'finishShopping' => ['finishShopping', fn () => []],
        'leaveList' => ['leaveList', fn () => []],
    ];
}

/**
 * Actions only the owner may use.
 *
 * @return array<string, array{0: string, 1: callable(ShoppingList): list<mixed>}>
 */
function ownerActions(): array
{
    return [
        'saveSettings' => ['saveSettings', fn () => ['Renamed', true, 'strike']],
        'deleteList' => ['deleteList', fn () => []],
        'removeMember' => ['removeMember', fn () => [1]],
        'transferOwnership' => ['transferOwnership', fn () => [1]],
        'regenerateInvite' => ['regenerateInvite', fn () => []],
        'removeCover' => ['removeCover', fn () => []],
    ];
}

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->owner->id]);
    $this->member = User::factory()->create();
    $this->list->members()->attach($this->member->id);
});

foreach (memberActions() as $label => [$method, $args]) {
    test("{$label} is refused once the member has been removed", function () use ($method, $args) {
        $this->actingAs($this->member);
        $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

        $arguments = $args($this->list);
        $this->list->members()->detach($this->member->id);

        $component->call($method, ...$arguments)->assertForbidden();
    });
}

foreach (ownerActions() as $label => [$method, $args]) {
    test("{$label} is refused for a plain member", function () use ($method, $args) {
        $this->actingAs($this->member);

        Livewire::test(Show::class, ['shoppingList' => $this->list])
            ->call($method, ...$args($this->list))
            ->assertForbidden();
    });

    test("{$label} is refused once the owner lost the list", function () use ($method, $args) {
        $this->actingAs($this->owner);
        $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

        $this->list->update(['owner_id' => $this->member->id]);

        $component->call($method, ...$args($this->list))->assertForbidden();
    });
}

test('items from another list cannot be touched through this list', function () {
    $foreign = ListItem::factory()->create();
    $this->actingAs($this->member);

    foreach (['toggleBought', 'toggleClaim', 'deleteItem'] as $method) {
        Livewire::test(Show::class, ['shoppingList' => $this->list])->call($method, $foreign->id)->assertNotFound();
    }

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveItem', $foreign->id, 'Hacked', null, 'ცალი', 'normal')
        ->assertNotFound();

    expect($foreign->fresh()->name)->not->toBe('Hacked')->and($foreign->fresh()->bought_at)->toBeNull();
});

test('polls from another list cannot be used through this list', function () {
    $foreign = Poll::factory()->create();
    $option = PollOption::factory()->create(['poll_id' => $foreign->id]);
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('vote', $foreign->id, $option->id)->assertNotFound();
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('promotePoll', $foreign->id, $option->id)->assertNotFound();
});

test('guests cannot reach any app page', function () {
    foreach (['lists.index', 'settings.profile'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    $this->get(route('lists.show', $this->list))->assertRedirect(route('login'));
});
