<?php

namespace App\Livewire\Lists;

use App\Enums\BoughtBehavior;
use App\Enums\Priority;
use App\Enums\Unit;
use App\Models\ListItem;
use App\Models\SavedItem;
use App\Models\ShoppingList;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('ჩემი სიები')]
class Index extends Component
{
    public string $name = '';

    public string $boughtBehavior = 'strike';

    public function createList(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'boughtBehavior' => ['required', 'in:strike,hide'],
        ], [
            'name.required' => 'დაწერე სიის სახელი.',
        ]);

        $list = ShoppingList::create([
            'owner_id' => auth()->id(),
            'name' => trim($validated['name']),
            'bought_behavior' => BoughtBehavior::from($validated['boughtBehavior']),
        ]);

        $list->members()->attach(auth()->id());

        $this->reset('name', 'boughtBehavior');
        $this->dispatch('list-created');
        $this->dispatch('toast', message: "სია „{$list->name}“ შეიქმნა");
    }

    public function markNotificationsRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function readd(int $savedItemId): void
    {
        $saved = auth()->user()->savedItems()->with('shoppingList')->findOrFail($savedItemId);

        $list = $saved->shoppingList;

        if (! $list || ! $list->members()->whereKey(auth()->id())->exists()) {
            return;
        }

        ListItem::create([
            'shopping_list_id' => $list->id,
            'added_by' => auth()->id(),
            'name' => $saved->name,
            'quantity' => $saved->quantity,
            'unit' => $saved->unit ?? Unit::Piece->value,
        ]);

        $this->dispatch('toast', message: "{$saved->name} დაემატა: {$list->name}");
    }

    public function render(): View
    {
        $user = auth()->user();

        /** @var Collection<int, ShoppingList> $lists */
        $lists = $user->shoppingLists()
            ->with('members')
            ->withCount([
                'items as left_count' => fn ($query) => $query->whereNull('bought_at'),
                'items as urgent_count' => fn ($query) => $query->whereNull('bought_at')->where('priority', Priority::Urgent),
                'items as done_count' => fn ($query) => $query->whereNotNull('bought_at'),
            ])
            ->latest('shopping_lists.created_at')
            ->get();

        return view('livewire.lists.index', [
            'notifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
            'lists' => $lists,
            'urgentTotal' => (int) $lists->sum('urgent_count'),
            'pinned' => SavedItem::query()->with('shoppingList')->where('user_id', $user->id)->whereNotNull('shopping_list_id')->latest()->limit(12)->get(),
            'greeting' => match (true) {
                now()->hour < 12 => 'დილა მშვიდობისა',
                now()->hour < 18 => 'დღე მშვიდობისა',
                default => 'საღამო მშვიდობისა',
            },
        ]);
    }
}
