<?php

use App\Models\ShoppingList;
use App\Models\User;

/**
 * Parse the palette variables out of app.css.
 *
 * @return array<string, array<string, string>>
 */
function palettes(): array
{
    $css = file_get_contents(resource_path('css/app.css'));
    preg_match_all("/(?::root,\s*)?\[data-palette='(\w+)'\]\s*\{([^}]*)\}/", $css, $blocks, PREG_SET_ORDER);

    $palettes = [];

    foreach ($blocks as $block) {
        preg_match_all('/--([\w-]+):\s*(#[0-9a-f]{6})/i', $block[2], $vars, PREG_SET_ORDER);
        $palettes[$block[1]] = array_column($vars, 2, 1);
    }

    return $palettes;
}

function luminance(string $hex): float
{
    $channels = array_map(function (string $part): float {
        $value = hexdec($part) / 255;

        return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }, str_split(ltrim($hex, '#'), 2));

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrast(string $a, string $b): float
{
    [$light, $dark] = [max(luminance($a), luminance($b)), min(luminance($a), luminance($b))];

    return ($light + 0.05) / ($dark + 0.05);
}

test('all five palettes are defined', function () {
    expect(array_keys(palettes()))->toBe(['night', 'matsoni', 'tarkhuna', 'churchkhela']);
});

test('text and accent colours meet WCAG AA on every surface in every palette', function () {
    $failures = [];

    foreach (palettes() as $name => $vars) {
        foreach (['ink-950', 'ink-900', 'ink-850'] as $surface) {
            foreach (['ink-100', 'ink-200', 'ink-300', 'ink-400', 'ink-500', 'p-fg', 'u-fg', 'c-fg'] as $text) {
                $ratio = contrast($vars[$text], $vars[$surface]);

                if ($ratio < 4.5) {
                    $failures[] = sprintf('%s: %s on %s = %.2f', $name, $text, $surface, $ratio);
                }
            }
        }

        foreach (['p400', 'u400', 'c400'] as $fill) {
            $ratio = contrast('#1a1621', $vars[$fill]);

            if ($ratio < 4.5) {
                $failures[] = sprintf('%s: on-colour text on %s = %.2f', $name, $fill, $ratio);
            }
        }
    }

    expect($failures)->toBe([]);
});

test('the theme boot script stays in sync with the palette names', function () {
    $head = file_get_contents(resource_path('views/layouts/partials/head.blade.php'));

    expect($head)->toContain("'matsoni'")->and($head)->toContain("'night'");
});

test('toasts are announced to screen readers', function () {
    expect(file_get_contents(resource_path('views/components/toast.blade.php')))
        ->toContain('role="status"')
        ->toContain('aria-live="polite"');
});

test('bottom sheets are dialogs that trap and restore focus', function () {
    $sheet = file_get_contents(resource_path('views/components/sheet.blade.php'));

    expect($sheet)->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('x-trap.noscroll');
});

test('reduced motion is respected', function () {
    expect(file_get_contents(resource_path('css/app.css')))->toContain('prefers-reduced-motion: reduce');
});

test('every page has a single h1 and landmarks', function () {
    $user = User::factory()->create();
    $list = ShoppingList::factory()->create(['owner_id' => $user->id]);

    foreach ([route('login'), route('register'), route('password.request')] as $url) {
        $html = $this->get($url)->getContent();
        expect(substr_count($html, '<h1'))->toBe(1);
        expect($html)->toContain('<main');
    }

    foreach ([route('lists.index'), route('lists.show', $list), route('settings.profile')] as $url) {
        $html = $this->actingAs($user)->get($url)->getContent();
        expect(substr_count($html, '<h1'))->toBe(1);
        expect($html)->toContain('<main');
    }
});
