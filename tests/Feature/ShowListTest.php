<?php

use App\Enums\BoughtBehavior;
use App\Enums\Priority;
use App\Enums\Unit;
use App\Livewire\Lists\Show;
use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Dato']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id, 'name' => 'Home list']);
});

test('guests are redirected to login', function () {
    $this->get(route('lists.show', $this->list))->assertRedirect(route('login'));
});

test('non members cannot open a list', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('lists.show', $this->list))->assertForbidden();
});

test('members can open a list', function () {
    $this->actingAs($this->user);

    $this->get(route('lists.show', $this->list))->assertOk()->assertSee('Home list');
});

test('a member can add an item with quantity and unit', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'Milk', 1.5, 'ლ', true)
        ->assertHasNoErrors();

    $item = $this->list->items()->firstOrFail();

    expect($item->name)->toBe('Milk')
        ->and($item->quantity)->toBe(1.5)
        ->and($item->unit)->toBe(Unit::Liter)
        ->and($item->priority)->toBe(Priority::Urgent)
        ->and($item->added_by)->toBe($this->user->id);
});

test('adding validates name and unit', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', '  ', null, 'ცალი', false)
        ->assertHasErrors('name');

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'Milk', null, 'bogus', false)
        ->assertHasErrors('unit');

    expect($this->list->items()->count())->toBe(0);
});

test('non members cannot add items', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertForbidden();
});

test('toggling bought records who bought it and toggles back', function () {
    $this->actingAs($this->user);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'claimed_by' => $this->user->id]);

    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleBought', $item->id);

    $item->refresh();
    expect($item->isBought())->toBeTrue()
        ->and($item->bought_by)->toBe($this->user->id)
        ->and($item->claimed_by)->toBeNull();

    $component->call('toggleBought', $item->id);

    expect($item->refresh()->isBought())->toBeFalse();
});

test('claiming is exclusive and can be released only by the claimer', function () {
    $other = User::factory()->create();
    $this->list->members()->attach($other->id);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id]);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleClaim', $item->id);
    expect($item->refresh()->claimed_by)->toBe($this->user->id);

    $this->actingAs($other);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleClaim', $item->id);
    expect($item->refresh()->claimed_by)->toBe($this->user->id);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleClaim', $item->id);
    expect($item->refresh()->claimed_by)->toBeNull();
});

test('items from other lists cannot be touched', function () {
    $this->actingAs($this->user);
    $foreign = ListItem::factory()->create();

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('toggleBought', $foreign->id)
        ->assertNotFound();

    expect($foreign->refresh()->isBought())->toBeFalse();
});

test('an item can be edited and deleted', function () {
    $this->actingAs($this->user);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Old']);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveItem', $item->id, 'New', 2.0, 'კგ', 'maybe')
        ->assertHasNoErrors()
        ->call('deleteItem', $item->id);

    expect(ListItem::find($item->id))->toBeNull();
});

test('clearing bought items only removes bought ones', function () {
    $this->actingAs($this->user);
    ListItem::factory()->bought()->count(2)->create(['shopping_list_id' => $this->list->id]);
    ListItem::factory()->create(['shopping_list_id' => $this->list->id]);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('clearBought');

    expect($this->list->items()->count())->toBe(1);
});

test('reopening brings bought items back', function () {
    $this->actingAs($this->user);
    ListItem::factory()->bought()->count(2)->create(['shopping_list_id' => $this->list->id]);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('reopenBought');

    expect($this->list->items()->whereNull('bought_at')->count())->toBe(2);
});

test('only the owner can change settings', function () {
    $member = User::factory()->create();
    $this->list->members()->attach($member->id);

    $this->actingAs($member);
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveSettings', 'Hacked', false, 'hide')
        ->assertForbidden();

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveSettings', 'Renamed', false, 'hide')
        ->assertHasNoErrors();

    $this->list->refresh();
    expect($this->list->name)->toBe('Renamed')
        ->and($this->list->group_by_priority)->toBeFalse()
        ->and($this->list->bought_behavior)->toBe(BoughtBehavior::Hide);
});

test('the owner can delete the list', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('deleteList')
        ->assertRedirect(route('lists.index'));

    expect(ShoppingList::find($this->list->id))->toBeNull();
});

test('a member can leave but the owner cannot', function () {
    $member = User::factory()->create();
    $this->list->members()->attach($member->id);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('leaveList');
    expect($this->list->members()->whereKey($this->user->id)->exists())->toBeTrue();

    $this->actingAs($member);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('leaveList')->assertRedirect(route('lists.index'));
    expect($this->list->members()->whereKey($member->id)->exists())->toBeFalse();
});

test('the list polls and shows items added by another member', function () {
    $other = User::factory()->create();
    $this->list->members()->attach($other->id);

    $this->actingAs($this->user);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->assertSeeHtml('wire:poll.visible.5s')
        ->assertDontSee('Bread');

    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'added_by' => $other->id, 'name' => 'Bread']);

    $component->call('$refresh')->assertSee('Bread');
});

test('an item can be added with the maybe priority', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'Tea', null, 'ცალი', false, 'maybe')
        ->assertHasNoErrors();

    expect($this->list->items()->first()->priority)->toBe(Priority::Maybe);
});

test('an unknown priority is rejected when adding', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'Tea', null, 'ცალი', false, 'bogus')
        ->assertHasErrors('priority');
});

test('the saved theme is rendered on the html element of every layout', function () {
    $this->user->update(['theme' => 'tarkhuna']);
    $this->actingAs($this->user);

    $this->get(route('lists.show', $this->list))->assertSee('data-palette="tarkhuna"', false);
    $this->get(route('lists.index'))->assertSee('data-palette="tarkhuna"', false);
    $this->get(route('settings.profile'))->assertSee('data-palette="tarkhuna"', false);
});

test('the system theme leaves the palette to the browser', function () {
    $this->actingAs($this->user);

    $this->get(route('lists.show', $this->list))->assertDontSee('data-palette="', false);
});
