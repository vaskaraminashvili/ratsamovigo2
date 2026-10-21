<?php

use App\Livewire\Lists\Show;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\ShoppingList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->list = ShoppingList::factory()->create(['owner_id' => $this->user->id]);
    $this->member = User::factory()->create();
    $this->list->members()->attach($this->member->id);

    $this->poll = Poll::factory()->create(['shopping_list_id' => $this->list->id, 'created_by' => $this->user->id, 'question' => 'Dinner?']);
    $this->pizza = PollOption::factory()->create(['poll_id' => $this->poll->id, 'label' => 'Pizza']);
    $this->soup = PollOption::factory()->create(['poll_id' => $this->poll->id, 'label' => 'Soup']);
});

test('a member can create a poll with 2 to 6 options', function () {
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->call('createPoll', '  Snack? ', ['Chips', ' Nuts ', '', 'Fruit'])
        ->assertHasNoErrors();

    $poll = Poll::where('question', 'Snack?')->firstOrFail();

    expect($poll->created_by)->toBe($this->member->id)
        ->and($poll->options()->pluck('label')->all())->toBe(['Chips', 'Nuts', 'Fruit']);
});

test('poll creation is validated', function () {
    $this->actingAs($this->member);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    $component->call('createPoll', '', ['A', 'B'])->assertHasErrors('question');
    $component->call('createPoll', 'Q?', ['A'])->assertHasErrors('options');
    $component->call('createPoll', 'Q?', ['A', 'B', 'C', 'D', 'E', 'F', 'G'])->assertHasErrors('options');
    $component->call('createPoll', 'Q?', ['Same', 'same'])->assertHasErrors('options.1');

    expect(Poll::count())->toBe(1);
});

test('a member can vote and change their vote', function () {
    $this->actingAs($this->member);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list]);

    $component->call('vote', $this->poll->id, $this->pizza->id);
    expect(PollVote::count())->toBe(1)->and(PollVote::first()->poll_option_id)->toBe($this->pizza->id);

    $component->call('vote', $this->poll->id, $this->soup->id);
    expect(PollVote::count())->toBe(1)->and(PollVote::first()->poll_option_id)->toBe($this->soup->id);
});

test('each member has one vote', function () {
    $this->actingAs($this->member);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('vote', $this->poll->id, $this->pizza->id);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('vote', $this->poll->id, $this->pizza->id);

    expect(PollVote::count())->toBe(2);
});

test('voting for an option of another poll is rejected', function () {
    $foreign = PollOption::factory()->create();
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('vote', $this->poll->id, $foreign->id)->assertStatus(404);

    expect(PollVote::count())->toBe(0);
});

test('non members cannot vote or see the poll', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['shoppingList' => $this->list])->assertForbidden();
});

test('percentages and leader are shown', function () {
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->pizza->id]);
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->pizza->id]);
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->soup->id]);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])
        ->assertSee('67%')
        ->assertSee('33%')
        ->assertSee('3 ხმა')
        ->assertSee('ლიდერობს: Pizza');
});

test('only the creator can delete a poll', function () {
    $this->actingAs($this->member);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('deletePoll', $this->poll->id)->assertStatus(404);
    expect(Poll::count())->toBe(1);

    $this->actingAs($this->user);
    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('deletePoll', $this->poll->id);
    expect(Poll::count())->toBe(0)->and(PollOption::where('poll_id', $this->poll->id)->count())->toBe(0);
});

test('the winner can be promoted to a list item and the poll closes', function () {
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->soup->id]);
    $this->actingAs($this->member);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('promotePoll', $this->poll->id, $this->soup->id);

    expect($this->list->items()->pluck('name')->all())->toBe(['Soup'])
        ->and(Poll::count())->toBe(0);
});

test('a trailing option or an unvoted poll cannot be promoted', function () {
    $this->actingAs($this->member);
    $component = Livewire::test(Show::class, ['shoppingList' => $this->list])->call('promotePoll', $this->poll->id, $this->soup->id);

    expect($this->list->items()->count())->toBe(0)->and(Poll::count())->toBe(1);

    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->pizza->id]);
    $component->call('promotePoll', $this->poll->id, $this->soup->id);

    expect($this->list->items()->count())->toBe(0);
});

test('ties allow promoting either leader', function () {
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->pizza->id]);
    PollVote::factory()->create(['poll_id' => $this->poll->id, 'poll_option_id' => $this->soup->id]);
    $this->actingAs($this->user);

    Livewire::test(Show::class, ['shoppingList' => $this->list])->call('promotePoll', $this->poll->id, $this->pizza->id);

    expect($this->list->items()->pluck('name')->all())->toBe(['Pizza']);
});
