<?php

use App\Support\ProLimits;

test('every page carries link-preview tags for Facebook', function () {
    foreach ([route('home'), route('login'), route('privacy')] as $url) {
        $this->get($url)
            ->assertSee('<meta property="og:image" content="'.url('/og.png').'">', false)
            ->assertSee('<meta property="og:locale" content="ka_GE">', false)
            ->assertSee('<meta name="description"', false);
    }
});

test('the share image exists at the size the tags promise', function () {
    [$width, $height] = getimagesize(public_path('og.png'));

    expect([$width, $height])->toBe([1200, 630]);
});

test('the landing page has the FAQ chat with the current price and a contact fallback', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('კითხვები და პასუხები')
        ->assertSee('როგორ დავამატო ოჯახი?')
        ->assertSee(ProLimits::PRICE)
        ->assertSee(config('mail.from.address'));
});

test('the landing page explains how to add the app to the home screen on both phones', function () {
    $this->get(route('home'))
        ->assertSee('დააყენე ტელეფონზე, როგორც აპი')
        ->assertSee('Add to Home Screen')
        ->assertSee('Install app')
        ->assertSee('ტელეფონზე დაყენება შეიძლება?');
});
