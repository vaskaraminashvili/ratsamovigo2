<?php

use App\Enums\BoughtBehavior;
use App\Livewire\Lists\Index;
use App\Livewire\Lists\Show;
use App\Livewire\Settings\Profile;
use App\Models\ListItem;
use App\Models\ShoppingList;
use App\Models\User;
use App\Support\ProLimits;
use Livewire\Livewire;

function listFor(User $owner, array $attributes = []): ShoppingList
{
    // The factory already adds the owner as a member.
    return ShoppingList::factory()->create(['owner_id' => $owner->id] + $attributes);
}

test('a free user can create one list but not a second', function () {
    $user = User::factory()->free()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->set('name', 'First')->call('createList')->assertHasNoErrors();
    Livewire::test(Index::class)->set('name', 'Second')->call('createList')->assertHasErrors(['name']);

    expect($user->shoppingLists()->count())->toBe(1);
});

test('a free user who joined someone elses list cannot create their own', function () {
    $owner = User::factory()->create();
    $user = User::factory()->free()->create();
    listFor($owner)->members()->attach($user->id);

    $this->actingAs($user);
    Livewire::test(Index::class)->set('name', 'Mine')->call('createList')->assertHasErrors(['name']);
});

test('a pro user can create many lists and use the hide behavior', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->set('name', 'One')->call('createList');
    Livewire::test(Index::class)->set('name', 'Two')->set('boughtBehavior', 'hide')->call('createList')->assertHasNoErrors();

    expect($user->shoppingLists()->count())->toBe(2)
        ->and(ShoppingList::where('name', 'Two')->first()->bought_behavior)->toBe(BoughtBehavior::Hide);
});

test('a free user always gets the strike behavior on a new list', function () {
    $user = User::factory()->free()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->set('name', 'One')->set('boughtBehavior', 'hide')->call('createList');

    expect(ShoppingList::first()->bought_behavior)->toBe(BoughtBehavior::Strike);
});

test('a free owner list holds two people and refuses a third', function () {
    $owner = User::factory()->free()->create();
    $list = listFor($owner);
    $second = User::factory()->free()->create();
    $third = User::factory()->free()->create();

    $this->actingAs($second)->post(route('join.store', $list->invite_token))->assertRedirect(route('lists.index'));
    $this->actingAs($third)->post(route('join.store', $list->invite_token))
        ->assertRedirect(route('join.show', $list->invite_token))
        ->assertSessionHasErrors('join');

    expect($list->members()->count())->toBe(2);
});

test('a pro owner list takes more than two people', function () {
    $list = listFor(User::factory()->create());

    foreach (User::factory()->free()->count(3)->create() as $user) {
        $this->actingAs($user)->post(route('join.store', $list->invite_token));
    }

    expect($list->members()->count())->toBe(4);
});

test('a free user already in a list cannot join another', function () {
    $user = User::factory()->free()->create();
    listFor($user);
    $other = listFor(User::factory()->create());

    $this->actingAs($user)->post(route('join.store', $other->invite_token))->assertSessionHasErrors('join');

    expect($other->members()->count())->toBe(1);
});

test('a member opening their own invite again is not blocked', function () {
    $user = User::factory()->free()->create();
    $list = listFor($user);

    $this->actingAs($user)->post(route('join.store', $list->invite_token))->assertSessionHasNoErrors();
});

test('a free owner cannot reopen bought items but a pro owner can', function (bool $isPro) {
    $owner = User::factory()->state(['is_pro' => $isPro])->create();
    $list = listFor($owner);
    $item = ListItem::factory()->bought()->create(['shopping_list_id' => $list->id]);

    $this->actingAs($owner);
    Livewire::test(Show::class, ['shoppingList' => $list])->call('reopenBought');

    expect($item->fresh()->bought_at === null)->toBe($isPro);
})->with([false, true]);

test('free settings changes rename the list but keep pro settings', function () {
    $owner = User::factory()->free()->create();
    $list = listFor($owner, ['group_by_priority' => true, 'fetch_best_price' => false, 'bought_behavior' => BoughtBehavior::Strike]);

    $this->actingAs($owner);
    Livewire::test(Show::class, ['shoppingList' => $list])->call('saveSettings', 'Renamed', false, 'hide', true);

    $list->refresh();
    expect($list->name)->toBe('Renamed')
        ->and($list->group_by_priority)->toBeTrue()
        ->and($list->fetch_best_price)->toBeFalse()
        ->and($list->bought_behavior)->toBe(BoughtBehavior::Strike);
});

test('pro settings only take effect on a pro owners list', function () {
    $list = listFor(User::factory()->free()->create(), ['fetch_best_price' => true]);

    $this->actingAs($list->owner);
    Livewire::test(Show::class, ['shoppingList' => $list])->assertSee('data-fetch-price="0"', false);

    $list->owner->forceFill(['is_pro' => true])->save();
    Livewire::test(Show::class, ['shoppingList' => $list->fresh()])->assertSee('data-fetch-price="1"', false);
});

test('a free user can pick night but not a pro theme', function () {
    $user = User::factory()->free()->create();
    $this->actingAs($user);

    Livewire::test(Profile::class)->call('setTheme', 'night');
    expect($user->fresh()->theme)->toBe('night');

    Livewire::test(Profile::class)->call('setTheme', 'tarkhuna');
    expect($user->fresh()->theme)->toBe('night');
});

test('a pro user can pick any theme', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Profile::class)->call('setTheme', 'tarkhuna');

    expect($user->fresh()->theme)->toBe('tarkhuna');
});

test('only pro users can save dishes from the profile', function (bool $isPro) {
    $user = User::factory()->state(['is_pro' => $isPro])->create();
    $this->actingAs($user);

    Livewire::test(Profile::class)->call('saveDish', null, 'Khinkali', [['name' => 'Flour', 'quantity' => 1, 'unit' => 'კგ']]);

    expect($user->dishes()->count())->toBe($isPro ? 1 : 0);
})->with([false, true]);

test('adding a dish to a list from a free account does not remember it', function () {
    $owner = User::factory()->free()->create();
    $list = listFor($owner);

    $this->actingAs($owner);
    Livewire::test(Show::class, ['shoppingList' => $list])
        ->call('addDish', 'Khinkali', [['name' => 'Flour', 'quantity' => 1, 'unit' => 'კგ']], true);

    expect($list->items()->count())->toBe(1)
        ->and($owner->dishes()->count())->toBe(0);
});

test('the user:pro command grants and revokes pro', function () {
    $user = User::factory()->free()->create();

    $this->artisan('user:pro', ['email' => $user->email])->assertSuccessful();
    expect($user->fresh()->isPro())->toBeTrue();

    $this->artisan('user:pro', ['email' => $user->email, '--revoke' => true])->assertSuccessful();
    expect($user->fresh()->isPro())->toBeFalse();

    $this->artisan('user:pro', ['email' => 'nobody@example.com'])->assertFailed();
});

test('the home page shows the pro price', function () {
    $this->get(route('home'))->assertOk()->assertSee(ProLimits::PRICE);
});
