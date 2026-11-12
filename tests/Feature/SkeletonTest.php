<?php

use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Features\SupportLazyLoading\SupportLazyLoading;

beforeEach(function () {
    // The suite switches lazy loading off; these tests need it on.
    config(['testing.lazy_pages' => true]);
    SupportLazyLoading::$disableWhileTesting = false;
});

test('the lists page first answers with a skeleton, not the lists', function () {
    $user = User::factory()->create();
    ShoppingList::factory()->create(['owner_id' => $user->id, 'name' => 'სახლის საყიდლები']);

    $this->actingAs($user)->get(route('lists.index'))
        ->assertOk()
        ->assertSee('skeleton', false)
        ->assertSee('aria-busy="true"', false)
        ->assertDontSee('სახლის საყიდლები');
});

test('a list page first answers with a skeleton, not the items', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id, 'name' => 'სახლის საყიდლები']);

    $this->actingAs($user)->get(route('lists.show', $list))
        ->assertOk()
        ->assertSee('skeleton', false)
        ->assertDontSee('სახლის საყიდლები');
});

test('a non-member still gets the 403 on the first request of a lazy list page', function () {
    $list = ShoppingList::factory()->create();

    $this->actingAs(User::factory()->create())->get(route('lists.show', $list))->assertForbidden();
});
