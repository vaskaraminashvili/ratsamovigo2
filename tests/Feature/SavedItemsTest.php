<?php

use App\Enums\Unit;
use App\Livewire\Lists\Show;
use App\Livewire\Settings\Profile;
use App\Models\ListItem;
use App\Models\SavedItem;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
});

test('names are normalized for matching', function () {
    expect(SavedItem::keyFor('  Milk   Fresh '))->toBe('milk fresh')
        ->and(SavedItem::keyFor('MILK'))->toBe(SavedItem::keyFor('milk'));
});

test('saving an item from a list stores it for the current user only', function () {
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Milk', 'quantity' => 2, 'unit' => Unit::Liter]);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveItem', $item->id, 'Milk', 2, 'ლ', 'normal', true);

    $saved = $this->user->savedItems()->firstOrFail();
    expect($saved->name)->toBe('Milk')
        ->and($saved->quantity)->toBe(2.0)
        ->and($saved->unit)->toBe('ლ')
        ->and($saved->shopping_list_id)->toBe($this->list->id)
        ->and(SavedItem::where('user_id', '!=', $this->user->id)->count())->toBe(0);
});

test('saving the same item twice with different casing keeps one record', function () {
    $this->user->savedItems()->create(['name' => 'milk']);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'MILK']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveItem', $item->id, 'MILK', null, 'ცალი', 'normal', true);

    expect($this->user->savedItems()->count())->toBe(1);
});

test('unsaving removes the saved item', function () {
    $this->user->savedItems()->create(['name' => 'Milk']);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'milk']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveItem', $item->id, 'milk', null, 'ცალი', 'normal', false);

    expect($this->user->savedItems()->count())->toBe(0);
});

test('omitting the saved flag leaves saved items untouched', function () {
    $this->user->savedItems()->create(['name' => 'Milk']);
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Milk']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('saveItem', $item->id, 'Milk', null, 'ცალი', 'normal');

    expect($this->user->savedItems()->count())->toBe(1);
});

test('saved items can be added to a list in bulk', function () {
    $milk = $this->user->savedItems()->create(['name' => 'Milk', 'quantity' => 1, 'unit' => 'ლ']);
    $bread = $this->user->savedItems()->create(['name' => 'Bread']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addSavedItems', [$milk->id, $bread->id]);

    expect($this->list->items()->orderBy('id')->pluck('name')->all())->toBe(['Milk', 'Bread'])
        ->and($this->list->items()->first()->unit)->toBe(Unit::Liter);
});

test('a user cannot add another users saved items', function () {
    $foreign = SavedItem::factory()->create(['name' => 'Secret']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addSavedItems', [$foreign->id]);

    expect($this->list->items()->count())->toBe(0);
});

test('non members cannot add saved items to a list', function () {
    $outsider = User::factory()->create();
    $saved = $outsider->savedItems()->create(['name' => 'Milk']);
    $this->actingAs($outsider);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertForbidden();

    expect($saved->exists)->toBeTrue()->and($this->list->items()->count())->toBe(0);
});

test('the list page shows saved suggestions and bookmarks', function () {
    $this->user->savedItems()->create(['name' => 'Honey']);
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Honey']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSee('Honey')->assertSee('შენახულია');
});

test('the profile can add and delete saved items', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('savedName', '  Tea ')
        ->set('savedQuantity', 2)
        ->set('savedUnit', 'შეკვრა')
        ->call('addSaved')
        ->assertHasNoErrors();

    $saved = $this->user->savedItems()->firstOrFail();
    expect($saved->name)->toBe('Tea')->and($saved->quantity)->toBe(2.0)->and($saved->unit)->toBe('შეკვრა');

    Livewire::test(Profile::class)->call('deleteSaved', $saved->id);

    expect($this->user->savedItems()->count())->toBe(0);
});

test('adding a saved item validates the name and unit', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('savedName', '  ')->call('addSaved')->assertHasErrors('savedName');
    Livewire::test(Profile::class)->set('savedName', 'Tea')->set('savedUnit', 'bogus')->call('addSaved')->assertHasErrors('savedUnit');
});

test('a user cannot delete another users saved item', function () {
    $foreign = SavedItem::factory()->create();
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->call('deleteSaved', $foreign->id);

    expect(SavedItem::find($foreign->id))->not->toBeNull();
});
