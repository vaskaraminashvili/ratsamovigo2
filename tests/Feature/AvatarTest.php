<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('a user can upload an avatar that is resized and stored as jpeg', function () {
    Livewire::test(Profile::class)
        ->set('avatar', UploadedFile::fake()->image('me.png', 1000, 1000))
        ->assertHasNoErrors();

    $path = $this->user->fresh()->avatar_path;

    expect($path)->toStartWith('avatars/')->and($path)->toEndWith('.jpg');
    expect(getimagesizefromstring(Storage::disk('public')->get($path))[0])->toBe(256);
});

test('avatars over 2 MB are rejected', function () {
    Livewire::test(Profile::class)
        ->set('avatar', UploadedFile::fake()->image('big.jpg')->size(3000))
        ->assertHasErrors('avatar');

    expect($this->user->fresh()->avatar_path)->toBeNull();
});

test('replacing and removing an avatar deletes the old file', function () {
    $component = Livewire::test(Profile::class)->set('avatar', UploadedFile::fake()->image('a.jpg'));
    $first = $this->user->fresh()->avatar_path;

    $component->set('avatar', UploadedFile::fake()->image('b.jpg'));
    $second = $this->user->fresh()->avatar_path;

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    $component->call('removeAvatar');

    Storage::disk('public')->assertMissing($second);
    expect($this->user->fresh()->avatar_path)->toBeNull();
});
