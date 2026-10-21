<?php

use App\Livewire\Lists\Show;
use App\Models\ListItem;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->owner = User::factory()->create(['name' => 'Owner']);
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->owner->id, 'name' => 'Home']);
    $this->nino = User::factory()->create(['name' => 'Nino']);
    $this->gio = User::factory()->create(['name' => 'Gio']);
    $this->list->members()->attach([$this->nino->id, $this->gio->id]);
});

test('the owner can remove a member and their claims and votes are released', function () {
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'claimed_by' => $this->nino->id]);
    $poll = Poll::factory()->create(['shopping_list_id' => $this->list->id, 'created_by' => $this->owner->id]);
    $option = PollOption::factory()->create(['poll_id' => $poll->id]);
    PollVote::factory()->create(['poll_id' => $poll->id, 'poll_option_id' => $option->id, 'user_id' => $this->nino->id]);
    $this->list->update(['shopping_by' => $this->nino->id, 'shopping_started_at' => now()]);

    $this->actingAs($this->owner);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeMember', $this->nino->id);

    expect($this->list->members()->whereKey($this->nino->id)->exists())->toBeFalse()
        ->and($item->fresh()->claimed_by)->toBeNull()
        ->and(PollVote::count())->toBe(0)
        ->and($this->list->fresh()->shopping_by)->toBeNull();
});

test('the owner cannot be removed', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeMember', $this->owner->id);

    expect($this->list->members()->whereKey($this->owner->id)->exists())->toBeTrue();
});

test('removing someone who is not a member returns 404', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeMember', User::factory()->create()->id)->assertStatus(404);
});

test('a regular member cannot remove others, transfer ownership or regenerate the invite', function () {
    $token = $this->list->invite_token;
    $this->actingAs($this->nino);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('removeMember', $this->gio->id)->assertForbidden();
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('transferOwnership', $this->gio->id)->assertForbidden();
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('regenerateInvite')->assertForbidden();

    expect($this->list->members()->count())->toBe(3)
        ->and($this->list->fresh()->owner_id)->toBe($this->owner->id)
        ->and($this->list->fresh()->invite_token)->toBe($token);
});

test('ownership can be transferred to another member', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('transferOwnership', $this->nino->id);

    expect($this->list->fresh()->owner_id)->toBe($this->nino->id);

    $this->actingAs($this->nino);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('saveSettings', 'Renamed', true, 'strike')->assertHasNoErrors();
    expect($this->list->fresh()->name)->toBe('Renamed');

    $this->actingAs($this->owner);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('saveSettings', 'Hacked', true, 'strike')->assertForbidden();
});

test('ownership can only go to a member', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('transferOwnership', User::factory()->create()->id)->assertStatus(404);

    expect($this->list->fresh()->owner_id)->toBe($this->owner->id);
});

test('regenerating the invite invalidates the old link', function () {
    $old = $this->list->invite_token;
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('regenerateInvite');

    $new = $this->list->fresh()->invite_token;
    expect($new)->not->toBe($old)->and(strlen($new))->toBe(32);

    $this->get(route('join.show', $old))->assertNotFound();
    $this->get(route('join.show', $new))->assertOk();
});

test('an existing member sees the open list button on the invite page', function () {
    $this->actingAs($this->nino)
        ->get(route('join.show', $this->list->invite_token))
        ->assertOk()
        ->assertSee('უკვე წევრი ხარ')
        ->assertSee(route('lists.show', $this->list), false)
        ->assertDontSee('სიას შეერთება');
});

test('a non member sees the join button on the invite page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('join.show', $this->list->invite_token))
        ->assertSee('სიას შეერთება');
});

test('leaving releases claims and shows feedback on the lists page', function () {
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'claimed_by' => $this->nino->id]);

    $this->actingAs($this->nino);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('leaveList')->assertRedirect(route('lists.index'));

    expect($item->fresh()->claimed_by)->toBeNull()
        ->and($this->list->members()->whereKey($this->nino->id)->exists())->toBeFalse();

    $this->withSession(['status' => 'დატოვე სია „Home“.'])->get(route('lists.index'))->assertSee('დატოვე სია „Home“.');
});

test('deleting a list shows feedback', function () {
    $this->actingAs($this->owner);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('deleteList');

    expect(session('status'))->toContain('Home');
});

test('bought items are offered as quick re add suggestions', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Milk', 'bought_at' => now(), 'bought_by' => $this->owner->id]);

    $this->actingAs($this->owner);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSeeHtml('reopenNow(');
});

test('reopening a bought item from the suggestion works', function () {
    $item = ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Milk', 'bought_at' => now(), 'bought_by' => $this->owner->id]);

    $this->actingAs($this->owner);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('toggleBought', $item->id);

    expect($item->fresh()->bought_at)->toBeNull();
});

test('items render with optimistic state hooks', function () {
    ListItem::factory()->create(['shopping_list_id' => $this->list->id, 'name' => 'Milk']);

    $this->actingAs($this->owner);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertSeeHtml('flip = !flip')->assertSeeHtml('claim = true');
});

test('friendly georgian error pages are shown', function () {
    $this->get('/definitely-missing')->assertNotFound()->assertSee('გვერდი ვერ მოიძებნა');

    $this->actingAs(User::factory()->create())->get(route('lists.show', $this->list))->assertForbidden()->assertSee('წვდომა არ გაქვს');
});
