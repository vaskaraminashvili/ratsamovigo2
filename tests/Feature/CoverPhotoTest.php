<?php

use App\Livewire\Lists\Show;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
    $this->member = User::factory()->create();
    $this->list->members()->attach($this->member->id);
});

test('the owner can upload a cover that is resized and stored as jpeg', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->set('cover', UploadedFile::fake()->image('cover.png', 2400, 1200))
        ->assertHasNoErrors();

    $path = $this->list->fresh()->cover_path;

    expect($path)->toStartWith('covers/')->and($path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($path);
    expect(getimagesizefromstring(Storage::disk('public')->get($path))[0])->toBe(1200);
});

test('small images are not upscaled', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->set('cover', UploadedFile::fake()->image('cover.jpg', 600, 300));

    expect(getimagesizefromstring(Storage::disk('public')->get($this->list->fresh()->cover_path))[0])->toBe(600);
});

test('replacing the cover removes the old file', function () {
    $this->actingAs($this->user);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->set('cover', UploadedFile::fake()->image('one.jpg', 400, 300));
    $first = $this->list->fresh()->cover_path;

    $component->set('cover', UploadedFile::fake()->image('two.jpg', 400, 300));
    $second = $this->list->fresh()->cover_path;

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

test('only images are accepted', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->set('cover', UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'))
        ->assertHasErrors('cover');

    expect($this->list->fresh()->cover_path)->toBeNull();
});

test('images over 2 MB are rejected', function () {
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->set('cover', UploadedFile::fake()->image('big.jpg')->size(2049))
        ->assertHasErrors('cover');

    expect($this->list->fresh()->cover_path)->toBeNull();
});

test('members who are not the owner cannot change the cover', function () {
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->set('cover', UploadedFile::fake()->image('cover.jpg'))
        ->assertForbidden();

    expect($this->list->fresh()->cover_path)->toBeNull();
});

test('the owner can remove the cover and the file is deleted', function () {
    Storage::disk('public')->put('covers/old.jpg', 'x');
    $this->list->update(['cover_path' => 'covers/old.jpg']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeCover');

    expect($this->list->fresh()->cover_path)->toBeNull();
    Storage::disk('public')->assertMissing('covers/old.jpg');
});

test('a member cannot remove the cover', function () {
    $this->list->update(['cover_path' => 'covers/old.jpg']);
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeCover')->assertForbidden();

    expect($this->list->fresh()->cover_path)->toBe('covers/old.jpg');
});

test('deleting the list deletes its cover file', function () {
    Storage::disk('public')->put('covers/old.jpg', 'x');
    $this->list->update(['cover_path' => 'covers/old.jpg']);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('deleteList');

    Storage::disk('public')->assertMissing('covers/old.jpg');
});

test('the cover is shown on the list page', function () {
    Storage::disk('public')->put('covers/old.jpg', 'x');
    $this->list->update(['cover_path' => 'covers/old.jpg']);
    $this->actingAs($this->member);

    $this->get(route('lists.show', $this->list))->assertSee('covers/old.jpg');
});
