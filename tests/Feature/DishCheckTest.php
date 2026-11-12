<?php

use App\Livewire\Lists\Show;
use App\Models\ListItem;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\User;
use App\Support\RecipeIngredient;
use Livewire\Livewire;

function dishCheckList(User $owner, bool $enabled = true): ShoppingList
{
    return ShoppingList::factory()->create(['owner_id' => $owner->id, 'dish_check' => $enabled]);
}

test('recipe lines are parsed into name, quantity and unit', function (string $line, string $name, ?float $quantity, ?string $unit) {
    expect(RecipeIngredient::parse($line))->toBe(['name' => $name, 'quantity' => $quantity, 'unit' => $unit]);
})->with([
    'name first' => ['ხახვი — 500 გრ (წვრილად დაკეპილი)', 'ხახვი', 500.0, 'გ'],
    'quantity first' => ['150 გრ დაფქული წითელი წიწაკა', 'დაფქული წითელი წიწაკა', 150.0, 'გ'],
    'unit that is not a list unit' => ['ხმელი ქინძი — 1 ჩაის კოვზი', 'ხმელი ქინძი — 1 ჩაის კოვზი', null, null],
    'no quantity' => ['მარილს და ძმარს გემოვნებით ვამატებთ', 'მარილს და ძმარს გემოვნებით ვამატებთ', null, null],
]);

test('the recipe import is repeatable', function () {
    $path = tempnam(sys_get_temp_dir(), 'rec');
    file_put_contents($path, json_encode([
        ['source' => 'recipe.ge', 'url' => 'https://x.test/a', 'title' => 'ლობიო', 'categories' => [], 'parts' => [['name' => null, 'ingredients' => ['ლობიო — 1 კგ']]], 'preparation' => []],
        ['title' => 'no url'],
    ]));

    $this->artisan('recipes:import', ['path' => $path])->assertSuccessful();
    $this->artisan('recipes:import', ['path' => $path])->assertSuccessful();

    expect(Recipe::count())->toBe(1);
});

test('recipe search is off unless the list has dish check', function () {
    $owner = User::factory()->create();
    Recipe::factory()->create(['title' => 'ლობიო']);
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => dishCheckList($owner, false)])
        ->call('searchRecipes', 'ლობიო')->assertReturned([]);

    Livewire::test(Show::class, ['shoppingList' => dishCheckList($owner)])
        ->call('searchRecipes', 'ლობიო')->assertReturned(fn (array $rows) => count($rows) === 1);
});

test('ingredients already on the list are flagged and only ticked ones are added', function () {
    $owner = User::factory()->create();
    $list = dishCheckList($owner);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ნიორი']);
    $recipe = Recipe::factory()->create(['title' => 'ლობიო']);
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => $list])
        ->call('recipeLines', $recipe->id)
        ->assertReturned(fn (array $lines) => $lines[0]['onList'] === false && $lines[1]['onList'] === true)
        ->call('addRecipeLines', $recipe->id, [0, 99]);

    expect($list->items()->where('group_name', 'ლობიო')->pluck('name')->all())->toBe(['ხახვი']);
});

test('dish items can be reordered inside their dish', function () {
    $owner = User::factory()->create();
    $list = dishCheckList($owner);
    [$first, $second, $third] = collect(['ა', 'ბ', 'გ'])->map(fn (string $name) => ListItem::factory()->create([
        'shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => $name, 'group_name' => 'ლობიო',
    ]))->all();
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => $list])->call('sortDishItem', $third->id, 0);

    $order = $list->items()->where('group_name', 'ლობიო')->orderBy('position')->pluck('id')->all();
    expect($order)->toBe([$third->id, $first->id, $second->id]);
});

test('a dish with every item bought moves to the bought section', function () {
    $owner = User::factory()->create();
    $list = dishCheckList($owner);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ა', 'group_name' => 'ლობიო', 'bought_at' => now()]);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ბ', 'group_name' => 'სუპი']);
    $this->actingAs($owner);

    $component = Livewire::test(Show::class, ['shoppingList' => $list]);

    expect($component->viewData('doneDishGroups')->keys()->all())->toBe(['ლობიო'])
        ->and($component->viewData('dishGroups')->keys()->all())->toBe(['სუპი']);
});

test('a free owner cannot switch dish check on', function () {
    $owner = User::factory()->free()->create();
    $list = dishCheckList($owner, false);
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => $list])->call('saveSettings', 'Renamed', false, 'strike', false, true);

    expect($list->fresh()->dish_check)->toBeFalse();
});

test('a dish added from a recipe opens its preparation steps', function () {
    $owner = User::factory()->create();
    $list = dishCheckList($owner);
    Recipe::factory()->create(['title' => 'ლობიო', 'preparation' => ['ლობიო დაალბეთ ღამით.', 'მოხარშეთ რბილად.']]);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ლობიო', 'group_name' => 'ლობიო']);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ხახვი', 'group_name' => 'ხელით კერძი']);
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => $list])
        ->assertSee('ლობიო დაალბეთ ღამით.')
        ->assertSee('მოხარშეთ რბილად.')
        ->assertViewHas('recipePreps', fn ($preps) => $preps->keys()->all() === ['ლობიო']);
});

test('preparation steps are not loaded when dish check is off', function () {
    $owner = User::factory()->create();
    $list = dishCheckList($owner, false);
    Recipe::factory()->create(['title' => 'ლობიო', 'preparation' => ['ლობიო დაალბეთ ღამით.']]);
    ListItem::factory()->create(['shopping_list_id' => $list->id, 'added_by' => $owner->id, 'name' => 'ლობიო', 'group_name' => 'ლობიო']);
    $this->actingAs($owner);

    Livewire::test(Show::class, ['shoppingList' => $list])->assertDontSee('ლობიო დაალბეთ ღამით.');
});
