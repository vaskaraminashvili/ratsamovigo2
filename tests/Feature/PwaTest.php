<?php

use App\Models\ShoppingList;
use App\Models\User;

test('the manifest is valid and its icons exist', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, 512, JSON_THROW_ON_ERROR);

    expect($manifest['display'])->toBe('standalone')
        ->and($manifest['lang'])->toBe('ka')
        ->and($manifest['start_url'])->toBe('/lists')
        ->and(collect($manifest['icons'])->pluck('sizes')->all())->toContain('192x192', '512x512')
        ->and(collect($manifest['icons'])->pluck('purpose')->all())->toContain('maskable');

    foreach ($manifest['icons'] as $icon) {
        expect(public_path(ltrim($icon['src'], '/')))->toBeFile();
        [$width, $height] = getimagesize(public_path(ltrim($icon['src'], '/')));
        expect($width.'x'.$height)->toBe($icon['sizes']);
    }
});

test('the service worker and offline page are shipped', function () {
    expect(public_path('sw.js'))->toBeFile()
        ->and(public_path('offline.html'))->toBeFile()
        ->and(file_get_contents(public_path('sw.js')))->toContain('/offline.html')
        ->and(file_get_contents(public_path('offline.html')))->toContain('lang="ka"');
});

test('pages link the manifest and icons and do not call google fonts', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('apple-touch-icon', false)
        ->assertSee('viewport-fit=cover', false)
        ->assertDontSee('fonts.googleapis.com', false)
        ->assertDontSee('fonts.gstatic.com', false);
});

test('the fonts are self hosted', function () {
    foreach (['bricolage-grotesque-latin', 'noto-sans-georgian-georgian', 'noto-sans-georgian-latin'] as $font) {
        expect(public_path("fonts/{$font}.woff2"))->toBeFile();
    }

    expect(file_get_contents(resource_path('css/app.css')))->toContain("url('/fonts/noto-sans-georgian-georgian.woff2')");
});

test('signed in pages include the offline banner and logout clears cached pages', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)->get(route('lists.index'))->assertSee('ოფლაინ ხარ', false);
    $this->actingAs($user)->get(route('lists.show', $list))->assertSee('ოფლაინ ხარ', false);

    expect(file_get_contents(resource_path('js/app.js')))->toContain("postMessage('clear-pages')");
});

test('interactive icon buttons keep a 44px touch target', function () {
    $violations = [];

    foreach (glob(resource_path('views/{,*/,*/*/}*.blade.php'), GLOB_BRACE) as $file) {
        foreach (file($file) as $number => $line) {
            if (str_contains($line, '<button') && preg_match('/\bsize-(8|9|10)\b|\bh-(8|9|10)\b/', $line)) {
                $violations[] = basename($file).':'.($number + 1);
            }
        }
    }

    expect($violations)->toBe([]);
});
