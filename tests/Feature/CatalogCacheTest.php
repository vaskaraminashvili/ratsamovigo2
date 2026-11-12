<?php

use App\Livewire\Settings\Profile;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config(['cache.stores.array.serialize' => true]);
    Cache::purge('array');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('cached saved items refresh when items are added, changed or deleted', function () {
    expect($this->user->cachedSavedItems())->toHaveCount(0);

    $saved = SavedItem::factory()->create(['user_id' => $this->user->id, 'name' => 'Milk']);
    expect($this->user->cachedSavedItems()->pluck('name')->all())->toBe(['Milk']);

    $saved->update(['name' => 'Bread']);
    expect($this->user->cachedSavedItems()->pluck('name')->all())->toBe(['Bread']);

    Livewire::test(Profile::class)->call('deleteSaved', $saved->id);
    expect($this->user->cachedSavedItems())->toHaveCount(0);
});

test('cached dishes refresh when ingredients or dishes change', function () {
    $dish = Dish::factory()->create(['user_id' => $this->user->id]);
    expect($this->user->cachedDishes()->first()->ingredients)->toHaveCount(0);

    DishIngredient::factory()->create(['dish_id' => $dish->id]);
    expect($this->user->cachedDishes()->first()->ingredients)->toHaveCount(1);

    Livewire::test(Profile::class)->call('deleteDish', $dish->id);
    expect($this->user->cachedDishes())->toHaveCount(0);
});

test('one user cache never shows another users data', function () {
    $other = User::factory()->create();
    SavedItem::factory()->create(['user_id' => $other->id, 'name' => 'Secret']);

    expect($this->user->cachedSavedItems())->toHaveCount(0)
        ->and($other->cachedSavedItems())->toHaveCount(1);
});
