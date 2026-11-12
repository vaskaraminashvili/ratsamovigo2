<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLazyLoading\SupportLazyLoading;
use Tests\TestCase;

use function Livewire\on;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Lazy pages would only render their skeleton; the tests assert on the real page. Livewire
// switches its "no lazy loading" flag back off whenever it flushes state, so set it again each time.
// A test can opt back in with config(['testing.lazy_pages' => true]).
pest()->beforeEach(function () {
    $disableLazyLoading = fn () => config('testing.lazy_pages') ? null : SupportLazyLoading::disableWhileTesting();

    $disableLazyLoading();
    on('flush-state', $disableLazyLoading);
})->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
