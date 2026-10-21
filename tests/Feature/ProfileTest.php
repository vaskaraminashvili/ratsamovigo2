<?php

use App\Livewire\Settings\Profile;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Dato', 'email' => 'dato@example.com', 'password' => 'secret-pass']);
});

test('guests are redirected to login', function () {
    $this->get(route('settings.profile'))->assertRedirect(route('login'));
});

test('a user can open the profile page', function () {
    $this->actingAs($this->user)->get(route('settings.profile'))->assertOk()->assertSee('dato@example.com');
});

test('the saved theme is used as the boot fallback', function () {
    $this->user->update(['theme' => 'tarkhuna']);

    $this->actingAs($this->user)->get(route('settings.profile'))->assertSee("choice = 'tarkhuna'", false);
});

test('a user can pick a theme', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->call('setTheme', 'churchkhela')->assertHasNoErrors();

    expect($this->user->fresh()->theme)->toBe('churchkhela');
});

test('an unknown theme is rejected', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->call('setTheme', 'neon')->assertHasErrors('theme');

    expect($this->user->fresh()->theme)->toBe('system');
});

test('a user can update name and email', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('name', '  Nino  ')
        ->set('email', 'nino@example.com')
        ->call('saveProfile')
        ->assertHasNoErrors();

    $fresh = $this->user->fresh();
    expect($fresh->name)->toBe('Nino')
        ->and($fresh->email)->toBe('nino@example.com')
        ->and($fresh->email_verified_at)->toBeNull();
});

test('keeping the same email keeps verification', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('name', 'Nino')->call('saveProfile')->assertHasNoErrors();

    expect($this->user->fresh()->email_verified_at)->not->toBeNull();
});

test('the email must stay unique', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('email', 'taken@example.com')->call('saveProfile')->assertHasErrors('email');
});

test('a user can change the password with the current one', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('currentPassword', 'secret-pass')
        ->set('newPassword', 'brand-new-pass')
        ->call('changePassword')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-pass', $this->user->fresh()->password))->toBeTrue();
});

test('changing the password needs the correct current password', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('currentPassword', 'wrong')
        ->set('newPassword', 'brand-new-pass')
        ->call('changePassword')
        ->assertHasErrors('currentPassword');

    Livewire::test(Profile::class)
        ->set('currentPassword', 'secret-pass')
        ->set('newPassword', 'short')
        ->call('changePassword')
        ->assertHasErrors('newPassword');
});

test('deleting the account needs the password', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('deletePassword', 'wrong')->call('deleteAccount')->assertHasErrors('deletePassword');

    expect(User::find($this->user->id))->not->toBeNull();
});

test('deleting the account removes the user and their solo lists', function () {
    $list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('deletePassword', 'secret-pass')->call('deleteAccount')->assertRedirect(route('home'));

    expect(User::find($this->user->id))->toBeNull()
        ->and(ShoppingList::find($list->id))->toBeNull();
    $this->assertGuest();
});

test('deleting is blocked while owning a list shared with others', function () {
    $list = ShoppingList::factory()->create(['owner_id' => $this->user->id, 'name' => 'Family']);
    $list->members()->attach(User::factory()->create()->id);
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('deletePassword', 'secret-pass')->call('deleteAccount')->assertHasErrors('deletePassword');

    expect(User::find($this->user->id))->not->toBeNull()
        ->and(ShoppingList::find($list->id))->not->toBeNull();
});

test('deleting is allowed when only a joined member of someone elses list', function () {
    $list = ShoppingList::factory()->create();
    $list->members()->attach($this->user->id);
    $this->actingAs($this->user);

    Livewire::test(Profile::class)->set('deletePassword', 'secret-pass')->call('deleteAccount')->assertRedirect(route('home'));

    expect(ShoppingList::find($list->id))->not->toBeNull();
});
