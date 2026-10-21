<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

test('guests can see login and register screens', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
});

test('authenticated users are redirected away from guest pages', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('login'))->assertRedirect(route('lists.index'));
});

test('a user can register', function () {
    Livewire::test(Register::class)
        ->set('name', 'Dato')
        ->set('email', 'dato@example.com')
        ->set('password', 'password123')
        ->call('register')
        ->assertRedirect(route('lists.index'));

    $this->assertAuthenticated();
    expect(User::where('email', 'dato@example.com')->exists())->toBeTrue();
});

test('registration validates input', function () {
    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('password', 'short')
        ->call('register')
        ->assertHasErrors(['name', 'email', 'password']);
});

test('registering after opening an invite joins the list', function () {
    $list = ShoppingList::factory()->create();

    $this->get(route('join.show', $list->invite_token))->assertOk();

    Livewire::test(Register::class)
        ->set('name', 'Nino')
        ->set('email', 'nino@example.com')
        ->set('password', 'password123')
        ->call('register');

    expect($list->members()->where('email', 'nino@example.com')->exists())->toBeTrue();
});

test('a user can log in with valid credentials', function () {
    $user = User::factory()->create(['password' => 'password123']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(route('lists.index'));

    $this->assertAuthenticatedAs($user);
});

test('login fails with a wrong password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('a user can log out', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});
