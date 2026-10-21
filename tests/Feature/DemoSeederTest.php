<?php

use App\Models\Dish;
use App\Models\ListItem;
use App\Models\Poll;
use App\Models\SavedItem;
use App\Models\ShoppingList;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoFamilySeeder;

test('the demo family is seeded with lists, items, a poll and dishes', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(3)
        ->and(ShoppingList::count())->toBe(2)
        ->and(ListItem::whereNotNull('group_name')->count())->toBe(3)
        ->and(ListItem::whereNotNull('bought_at')->count())->toBeGreaterThan(0)
        ->and(ListItem::whereNotNull('claimed_by')->count())->toBe(1)
        ->and(Poll::count())->toBe(1)
        ->and(Poll::first()->votes()->count())->toBe(3)
        ->and(Dish::first()->ingredients()->count())->toBe(4)
        ->and(SavedItem::count())->toBe(3);

    $home = ShoppingList::where('name', 'სახლის საყიდლები')->firstOrFail();
    expect($home->members()->count())->toBe(3)->and(strlen($home->invite_token))->toBe(32);
});

test('seeding twice does not duplicate the family', function () {
    $this->seed(DemoFamilySeeder::class);
    $this->seed(DemoFamilySeeder::class);

    expect(User::count())->toBe(3)->and(ShoppingList::count())->toBe(2);
});

test('demo accounts can log in with the shared password', function () {
    $this->seed(DatabaseSeeder::class);

    $this->post(route('logout'));
    expect(auth()->attempt(['email' => 'dato@example.com', 'password' => 'password']))->toBeTrue();
});

test('the demo pages render for the seeded members', function () {
    $this->seed(DatabaseSeeder::class);
    $dato = User::where('email', 'dato@example.com')->firstOrFail();

    $this->actingAs($dato)->get(route('lists.index'))->assertOk()->assertSee('სახლის საყიდლები')->assertSee('ყოველდღიური');
    $this->get(route('lists.show', ShoppingList::where('name', 'სახლის საყიდლები')->firstOrFail()))
        ->assertOk()
        ->assertSee('რა ვივახშმოთ?')
        ->assertSee('ხაჭაპური');
    $this->get(route('settings.profile'))->assertOk()->assertSee('ხინკალი');
});

test('production never gets demo data', function () {
    $this->app['env'] = 'production';

    app(DatabaseSeeder::class)->run();

    expect(User::count())->toBe(0);
});
