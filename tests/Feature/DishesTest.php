<?php

use App\Enums\Unit;
use App\Livewire\Lists\Show;
use App\Livewire\Settings\Profile;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('a dish can be added to a list as a group', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', 'Khinkali', [
            ['name' => 'Flour', 'qty' => 1, 'unit' => 'კგ'],
            ['name' => 'Meat', 'qty' => '0.5', 'unit' => 'კგ'],
            ['name' => '   ', 'qty' => null, 'unit' => 'ცალი'],
        ], false)
        ->assertHasNoErrors();

    $items = $this->list->items()->orderBy('id')->get();

    expect($items)->toHaveCount(2)
        ->and($items->pluck('group_name')->unique()->all())->toBe(['Khinkali'])
        ->and($items[1]->quantity)->toBe(0.5)
        ->and($items[1]->unit)->toBe(Unit::Kilogram)
        ->and(Dish::count())->toBe(0);
});

test('a dish can be remembered when adding', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', 'Khinkali', [['name' => 'Flour', 'qty' => 1, 'unit' => 'კგ'], ['name' => 'Salt', 'qty' => null, 'unit' => 'ცალი']], true);

    $dish = $this->user->dishes()->with('ingredients')->firstOrFail();

    expect($dish->name)->toBe('Khinkali')
        ->and($dish->ingredients->pluck('name')->all())->toBe(['Flour', 'Salt'])
        ->and($dish->ingredients[1]->unit)->toBeNull();
});

test('remembering the same dish again replaces its ingredients', function () {
    $rows = fn (string $name) => [['name' => $name, 'qty' => null, 'unit' => 'ცალი']];

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', 'Soup', $rows('Carrot'), true)
        ->call('addDish', 'Soup', $rows('Onion'), true);

    expect($this->user->dishes()->count())->toBe(1)
        ->and($this->user->dishes()->first()->ingredients()->pluck('name')->all())->toBe(['Onion']);
});

test('a dish needs a name and at least one ingredient', function () {
    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', ' ', [['name' => 'Flour', 'qty' => null, 'unit' => 'ცალი']], false)
        ->assertHasErrors('name');

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', 'Soup', [['name' => ' ', 'qty' => null, 'unit' => 'ცალი']], false)
        ->assertHasErrors('ingredients');

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDish', 'Soup', [['name' => 'Salt', 'qty' => -1, 'unit' => 'ცალი']], false)
        ->assertHasErrors('ingredients.0.qty');

    expect($this->list->items()->count())->toBe(0);
});

test('chosen ingredients of a saved dish are added', function () {
    $dish = Dish::factory()->create(['user_id' => $this->user->id, 'name' => 'Salad']);
    $tomato = DishIngredient::factory()->create(['dish_id' => $dish->id, 'name' => 'Tomato', 'quantity' => 2, 'unit' => 'ცალი']);
    DishIngredient::factory()->create(['dish_id' => $dish->id, 'name' => 'Cucumber']);
    $foreign = DishIngredient::factory()->create(['name' => 'Foreign']);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDishIngredients', $dish->id, [$tomato->id, $foreign->id]);

    $items = $this->list->items()->get();

    expect($items)->toHaveCount(1)
        ->and($items[0]->name)->toBe('Tomato')
        ->and($items[0]->group_name)->toBe('Salad')
        ->and($items[0]->quantity)->toBe(2.0);
});

test('a user cannot use another users dish', function () {
    $dish = Dish::factory()->create();

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addDishIngredients', $dish->id, [])
        ->assertStatus(404);
});

test('a group can be toggled as a whole', function () {
    ListItem::factory()->count(2)->create(['shopping_list_id' => $this->list->id, 'group_name' => 'Soup']);
    $other = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'group_name' => null]);

    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleGroup', 'Soup');

    expect($this->list->items()->where('group_name', 'Soup')->whereNotNull('bought_at')->count())->toBe(2)
        ->and($other->fresh()->bought_at)->toBeNull();

    $component->call('toggleGroup', 'Soup');

    expect($this->list->items()->where('group_name', 'Soup')->whereNull('bought_at')->count())->toBe(2);
});

test('a group can be removed', function () {
    ListItem::factory()->count(2)->create(['shopping_list_id' => $this->list->id, 'group_name' => 'Soup']);
    $other = ListItem::factory()->create(['shopping_list_id' => $this->list->id]);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('deleteGroup', 'Soup');

    expect($this->list->items()->pluck('id')->all())->toBe([$other->id]);
});

test('groups render as dish sections and count towards the left total', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'group_name' => 'Soup', 'name' => 'Carrot']);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSee('Soup')->assertSee('Carrot')->assertSee('1 დარჩა');
});

test('non members cannot touch groups', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'group_name' => 'Soup']);
    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertForbidden();
});

test('the profile can create, edit and delete a dish', function () {
    Livewire::test(Profile::class)
        ->call('saveDish', null, 'Pasta', [['name' => 'Spaghetti', 'qty' => 500, 'unit' => 'გ'], ['name' => 'Salt', 'qty' => null, 'unit' => 'ცალი']])
        ->assertHasNoErrors();

    $dish = $this->user->dishes()->with('ingredients')->firstOrFail();
    expect($dish->ingredients)->toHaveCount(2)->and($dish->ingredients[0]->quantity)->toBe(500.0);

    Livewire::test(Profile::class)
        ->call('saveDish', $dish->id, 'Carbonara', [['name' => 'Egg', 'qty' => 3, 'unit' => 'ცალი']]);

    $dish->refresh()->load('ingredients');
    expect($dish->name)->toBe('Carbonara')->and($dish->ingredients->pluck('name')->all())->toBe(['Egg']);

    Livewire::test(Profile::class)->call('deleteDish', $dish->id);

    expect(Dish::count())->toBe(0)->and(DishIngredient::count())->toBe(0);
});

test('the profile validates dishes', function () {
    Livewire::test(Profile::class)->call('saveDish', null, '', [['name' => 'Egg', 'qty' => 1, 'unit' => 'ცალი']])->assertHasErrors('name');
    Livewire::test(Profile::class)->call('saveDish', null, 'Omelette', [])->assertHasErrors('ingredients');
    Livewire::test(Profile::class)->call('saveDish', null, 'Omelette', [['name' => 'Egg', 'qty' => 1, 'unit' => 'bogus']])->assertHasErrors('ingredients.0.unit');
});

test('a user cannot edit or delete another users dish', function () {
    $foreign = Dish::factory()->create(['name' => 'Foreign']);

    Livewire::test(Profile::class)->call('saveDish', $foreign->id, 'Hacked', [['name' => 'Egg', 'qty' => 1, 'unit' => 'ცალი']])->assertStatus(404);
    Livewire::test(Profile::class)->call('deleteDish', $foreign->id);

    expect($foreign->fresh()->name)->toBe('Foreign');
});
