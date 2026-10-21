<?php

use App\Livewire\Lists\Show;
use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Models\User;
use App\Support\ListActivity;
use Livewire\Livewire;

beforeEach(function () {
    $this->owner = User::factory()->create(['name' => 'Dato']);
    $this->guest = User::factory()->create(['name' => 'Nika']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->owner->id]);
    $this->list->members()->attach($this->guest);
});

it('builds a newest first feed of adds, claims, purchases and polls', function () {
    $this->travelTo(now()->subHours(3));
    $milk = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'added_by' => $this->owner->id, 'name' => 'რძე']);

    $this->travelTo(now()->addHour());
    $milk->update(['bought_by' => $this->guest->id, 'bought_at' => now()]);

    $this->travelTo(now()->addHour());
    $poll = $this->list->polls()->create(['created_by' => $this->owner->id, 'question' => 'რა ვიყიდოთ?']);

    $items = $this->list->items()->with(['adder', 'claimer', 'buyer'])->get();
    $polls = $this->list->polls()->with(['creator', 'options.votes.user'])->get();

    $texts = ListActivity::events($items, $polls)->map(fn (array $event) => $event['user']->name.' '.$event['text'])->all();

    expect($texts)->toBe([
        'Dato შექმნა გამოკითხვა: რა ვიყიდოთ?',
        'Nika იყიდა რძე',
        'Dato დაამატა რძე',
    ]);
});

it('collapses the ingredients of one dish into a single event', function () {
    foreach (['პასტა', 'ტომატი', 'ყველი'] as $name) {
        ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'added_by' => $this->owner->id, 'name' => $name, 'group_name' => 'ვახშამი']);
    }

    $events = ListActivity::events($this->list->items()->with(['adder', 'claimer', 'buyer'])->get(), collect());

    expect($events)->toHaveCount(1)
        ->and($events[0]['text'])->toBe('დაამატა კერძი „ვახშამი“');
});

it('shows the activity tab to members', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'added_by' => $this->guest->id, 'name' => 'პური']);
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->assertSee('აქტივობა')
        ->assertSee('Nika')
        ->assertSee('დაამატა პური');
});

it('keeps the added by text out of the item row and in the edit sheet data', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'added_by' => $this->guest->id, 'name' => 'პური']);
    $this->actingAs($this->owner);

    $html = Livewire::test(Show::class, ['shoppingList' => $this->list])->html();

    expect($html)->not->toContain('>დაამატა Nika<')
        ->and($html)->toContain('დაამატა Nika');
});
