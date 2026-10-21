<?php

use App\Livewire\Lists\Show;
use App\Models\ShoppingList;
use App\Models\User;
use App\Support\GeorgianLatin;
use App\Support\ProductSearch;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();

    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id, 'fetch_best_price' => true]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productRow(array $overrides = []): array
{
    return array_merge([
        'id' => 46214,
        'name' => 'ნაბეღლავი შუშხუნა ბოთლი 0.5ლ',
        'original_price' => '1.50',
        'sale_price' => '1.39',
        'unit' => '500 მლ',
        'store_slug' => 'carrefour',
        'store_name' => 'კარფური',
        'product_url' => 'https://tarifebi.ge/p/46214',
    ], $overrides);
}

it('turns latin letters into georgian', function () {
    $latin = app(GeorgianLatin::class);

    expect($latin->toGeorgian('nabeghlavi'))->toBe('ნაბეღლავი')
        ->and($latin->toGeorgian('sh'))->toBe('შ')
        ->and($latin->toGeorgian('Khachapuri'))->toBe('ხაჩაპური')
        ->and($latin->toGeorgian('ნაბეღლავი'))->toBe('ნაბეღლავი')
        ->and($latin->toGeorgian('123'))->toBe('123');
});

it('does not call the api for fewer than 3 characters', function () {
    Http::fake();
    $search = app(ProductSearch::class);

    expect($search->suggestions('შა'))->toBe([])
        ->and($search->suggestions('sh'))->toBe([]);

    Http::assertNothingSent();
});

it('searches in georgian, keeps five rows and caches the result', function () {
    Http::fake([
        'tarifebi.ge/*' => Http::response([
            'success' => true,
            'products' => [
                productRow(),
                productRow(['id' => 2, 'sale_price' => null, 'original_price' => '1.50', 'store_name' => 'მაგნიტი', 'unit' => null]),
                productRow(['id' => 3]),
                productRow(['id' => 4]),
                productRow(['id' => 5]),
                productRow(['id' => 6]),
            ],
        ]),
    ]);

    $rows = app(ProductSearch::class)->suggestions('nabeghlavi');

    expect($rows)->toHaveCount(5)
        ->and($rows[0]['price'])->toBe('1.39')
        ->and($rows[0]['caption'])->toBe('1.39 ₾ · კარფური · 500 მლ')
        ->and($rows[1]['price'])->toBe('1.50')
        ->and($rows[1]['unit'])->toBeNull()
        ->and($rows[1]['caption'])->toBe('1.50 ₾ · მაგნიტი');

    Http::assertSent(function (Request $request) {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['q'] === 'ნაბეღლავი'
            && $query['limit'] === '5'
            && $query['store_type'] === 'grocery'
            && $query['group'] === '1'
            && $query['sort'] === 'sale';
    });

    app(ProductSearch::class)->suggestions('nabeghlavi');

    Http::assertSentCount(1);
});

it('returns nothing when the api fails', function () {
    Http::fake(['tarifebi.ge/*' => Http::failedConnection()]);
    expect(app(ProductSearch::class)->suggestions('ნაბეღლავი'))->toBe([]);

    Http::fake(['tarifebi.ge/*' => Http::response(['success' => false], 500)]);
    expect(app(ProductSearch::class)->suggestions('პური'))->toBe([]);
});

it('ignores product links that are not https', function () {
    Http::fake([
        'tarifebi.ge/*' => Http::response(['success' => true, 'products' => [productRow(['product_url' => 'javascript:alert(1)'])]]),
    ]);

    expect(app(ProductSearch::class)->suggestions('ნაბეღლავი')[0]['url'])->toBeNull();
});

it('returns suggestions from the list when fetch best price is on', function () {
    Http::fake(['tarifebi.ge/*' => Http::response(['success' => true, 'products' => [productRow()]])]);
    $this->actingAs($this->user);

    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('searchProducts', 'nabeghlavi');

    expect($component->effects['returns'][0])->toHaveCount(1);
});

it('does not search when fetch best price is off', function () {
    Http::fake();
    $this->list->update(['fetch_best_price' => false]);
    $this->actingAs($this->user);

    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('searchProducts', 'nabeghlavi');

    expect($component->effects['returns'][0])->toBe([]);
    Http::assertNothingSent();
});

it('keeps the picked product on the new item', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'ნაბეღლავი შუშხუნა ბოთლი 0.5ლ', 2, 'ცალი', false, 'normal', [
            'product_id' => 46214,
            'store_name' => 'კარფური',
            'store_slug' => 'carrefour',
            'price' => '1.39',
            'unit' => '500 მლ',
            'url' => 'https://tarifebi.ge/p/46214',
        ])
        ->assertHasNoErrors();

    $item = $this->list->items()->firstOrFail();

    expect($item->catalog_product_id)->toBe(46214)
        ->and($item->catalog_store_name)->toBe('კარფური')
        ->and($item->catalog_price)->toBe('1.39')
        ->and($item->catalogCaption())->toBe('1.39 ₾ · კარფური · 500 მლ');
});

it('rejects a catalog link that is not https', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('addItem', 'პური', null, 'ცალი', false, 'normal', ['store_name' => 'სპარი', 'url' => 'http://evil.test'])
        ->assertHasErrors(['catalog.url']);

    expect($this->list->items()->count())->toBe(0);
});

it('leaves hand typed items without catalog data', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('addItem', 'კვერცხი', null, 'ცალი', false);

    $item = $this->list->items()->firstOrFail();

    expect($item->catalog_store_name)->toBeNull()
        ->and($item->catalogCaption())->toBeNull();
});

it('lets the owner turn fetch best price on and off', function () {
    $this->list->update(['fetch_best_price' => false]);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveSettings', 'სია', true, 'strike', true);

    expect($this->list->fresh()->fetch_best_price)->toBeTrue();

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveSettings', 'სია', true, 'strike');

    expect($this->list->fresh()->fetch_best_price)->toBeTrue();

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('saveSettings', 'სია', true, 'strike', false);

    expect($this->list->fresh()->fetch_best_price)->toBeFalse();
});
