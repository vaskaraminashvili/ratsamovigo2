<?php

use App\Models\ShoppingList;
use App\Models\User;

test('an unknown invite token returns 404', function () {
    $this->get(route('join.show', 'nope'))->assertNotFound();
});

test('guests see the invite page and the token is remembered', function () {
    $list = ShoppingList::factory()->create(['name' => 'Home list']);

    $this->get(route('join.show', $list->invite_token))
        ->assertOk()
        ->assertSee('Home list')
        ->assertSee(route('register'), false)
        ->assertSessionHas('invite_token', $list->invite_token);
});

test('a logged in user can join a list from an invite', function () {
    $list = ShoppingList::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('join.store', $list->invite_token))
        ->assertRedirect(route('lists.index'));

    expect($list->members()->whereKey($user->id)->exists())->toBeTrue();
});

test('joining twice does not duplicate membership', function () {
    $list = ShoppingList::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->post(route('join.store', $list->invite_token));
    $this->post(route('join.store', $list->invite_token));

    expect($list->members()->whereKey($user->id)->count())->toBe(1);
});

test('guests cannot post a join', function () {
    $list = ShoppingList::factory()->create();

    $this->post(route('join.store', $list->invite_token))->assertRedirect(route('login'));
});
