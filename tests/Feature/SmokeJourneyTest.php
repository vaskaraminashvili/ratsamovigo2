<?php

use App\Livewire\Auth\Register;
use App\Livewire\Lists\Index;
use App\Livewire\Lists\Show;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

/**
 * The whole story in one go: register, create a list, add an item, invite someone,
 * they join through the link and check the item off, and the first person sees it.
 */
test('two people can share a list from registration to checking an item off', function () {
    // 1. The first person registers.
    Livewire::test(Register::class)
        ->set('name', 'დათო')
        ->set('email', 'dato@example.com')
        ->set('password', 'password-123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('lists.index'));

    $dato = User::where('email', 'dato@example.com')->firstOrFail();
    $this->assertAuthenticatedAs($dato);

    // 2. They create a list and see it on their lists page.
    Livewire::test(Index::class)
        ->set('name', 'სახლი')
        ->call('createList')
        ->assertHasNoErrors();

    $list = ShoppingList::where('name', 'სახლი')->firstOrFail();
    $this->get(route('lists.index'))->assertOk()->assertSee('სახლი');

    // 3. They add an item.
    Livewire::test(Show::class, ['shoppingList' => $list])
        ->call('addItem', 'რძე', 2, 'ლ', true)
        ->assertHasNoErrors()
        ->assertSee('რძე');

    $item = $list->items()->firstOrFail();
    expect($item->quantityLabel())->toBe('2 ლ');

    // 4. They share the invite link, which a guest opens.
    $inviteUrl = route('join.show', $list->invite_token);
    $this->post(route('logout'))->assertRedirect();
    $this->assertGuest();

    $this->get($inviteUrl)->assertOk()->assertSee('სახლი')->assertSessionHas('invite_token', $list->invite_token);

    // 5. The guest registers and lands in the list automatically.
    Livewire::test(Register::class)
        ->set('name', 'ნინო')
        ->set('email', 'nino@example.com')
        ->set('password', 'password-123')
        ->call('register')
        ->assertHasNoErrors();

    $nino = User::where('email', 'nino@example.com')->firstOrFail();
    expect($list->members()->whereKey($nino->id)->exists())->toBeTrue();

    // 6. The second person claims and checks the item off.
    Livewire::test(Show::class, ['shoppingList' => $list])
        ->assertSee('რძე')
        ->call('toggleClaim', $item->id)
        ->call('toggleBought', $item->id);

    expect($item->fresh()->isBought())->toBeTrue()
        ->and($item->fresh()->bought_by)->toBe($nino->id)
        ->and($item->fresh()->claimed_by)->toBeNull();

    // 7. The first person sees it as bought.
    $this->post(route('logout'));
    $this->actingAs($dato);

    Livewire::test(Show::class, ['shoppingList' => $list])->assertSee('ნინო')->assertSee('იყიდა ნინო');
});

test('a stranger cannot open the list without the invite', function () {
    $list = ShoppingList::factory()->create();

    $this->actingAs(User::factory()->create())->get(route('lists.show', $list))->assertForbidden();
});
