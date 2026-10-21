<?php

use App\Models\User;

test('the privacy page is public and covers accounts, invites and deletion', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('კონფიდენციალურობის პოლიტიკა')
        ->assertSee('მოსაწვევ ბმულს')
        ->assertSee('მონაცემების წაშლა')
        ->assertSee(config('mail.from.address'));
});

test('the terms page is public and covers invite links', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('გამოყენების პირობები')
        ->assertSee('მოსაწვევი ბმულები');
});

test('the landing and register pages link to both documents', function () {
    foreach ([route('home'), route('register')] as $url) {
        $this->get($url)->assertSee(route('privacy'), false)->assertSee(route('terms'), false);
    }
});

test('the legal pages are also reachable when signed in', function () {
    $this->actingAs(User::factory()->create())->get(route('privacy'))->assertOk();
});
