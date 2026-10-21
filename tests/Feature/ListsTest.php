<?php

use App\Enums\BoughtBehavior;
use App\Livewire\Lists\Index;
use App\Models\ListItem;
use App\Models\SavedItem;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login', function () {
    $this->get(route('lists.index'))->assertRedirect(route('login'));
});

test('users only see lists they are a member of', function () {
    $user = User::factory()->create();
    $mine = ShoppingList::factory()->create(['owner_id' => $user->id, 'name' => 'Home list']);
    ShoppingList::factory()->create(['name' => 'Someone elses list']);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('Home list')
        ->assertDontSee('Someone elses list');
});

test('the urgent count only includes items that are not bought', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id]);
    ListItem::factory()->urgent()->count(2)->create(['shopping_list_id' => $list->id, 'added_by' => $user->id]);
    ListItem::factory()->urgent()->bought()->create(['shopping_list_id' => $list->id, 'added_by' => $user->id]);

    $this->actingAs($user);

    Livewire::test(Index::class)->assertSee('2 სასწრაფო ნივთია');
});

test('a user can create a list and becomes a member', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('name', 'Weekend')
        ->set('boughtBehavior', 'hide')
        ->call('createList')
        ->assertHasNoErrors();

    $list = ShoppingList::where('name', 'Weekend')->firstOrFail();

    expect($list->bought_behavior)->toBe(BoughtBehavior::Hide)
        ->and($list->invite_token)->not->toBeEmpty()
        ->and($list->members()->whereKey($user->id)->exists())->toBeTrue();
});

test('a list needs a name', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->set('name', '')
        ->call('createList')
        ->assertHasErrors(['name' => 'required']);
});

test('a saved item can be added back to its list', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id]);
    $saved = SavedItem::factory()->create(['user_id' => $user->id, 'shopping_list_id' => $list->id, 'name' => 'Matsoni']);

    $this->actingAs($user);

    Livewire::test(Index::class)->call('readd', $saved->id);

    expect($list->items()->where('name', 'Matsoni')->exists())->toBeTrue();
});

test('a user cannot re-add another users saved item', function () {
    $this->actingAs(User::factory()->create());
    $saved = SavedItem::factory()->create();

    Livewire::test(Index::class)->call('readd', $saved->id)->assertNotFound();
});

test('the lists page polls and reflects newly added items', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id, 'name' => 'Home']);

    $this->actingAs($user);
    $component = Livewire::test(Index::class)->assertSeeHtml('wire:poll.visible.10s')->assertSee('0 დარჩა');

    ListItem::factory()->create(['shopping_list_id' => $list->id]);

    $component->call('$refresh')->assertSee('1 დარჩა');
});
