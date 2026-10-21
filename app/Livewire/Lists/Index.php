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

    /**
     * Move a list to a new place in the current user's own ordering.
     */
    public function sortList(int $listId, int $position): void
    {
        $user = auth()->user();
        $user->shoppingLists()->whereKey($listId)->firstOrFail();

        $ids = $user->shoppingLists()
            ->orderByRaw('shopping_list_user.position is not null')
            ->orderBy('shopping_list_user.position')
            ->latest('shopping_lists.created_at')
            ->pluck('shopping_lists.id')
            ->reject(fn (int $id) => $id === $listId)
            ->values()
            ->all();

        array_splice($ids, max(0, min($position, count($ids))), 0, [$listId]);

        foreach ($ids as $index => $id) {
            $user->shoppingLists()->updateExistingPivot($id, ['position' => $index + 1]);
        }
    }

    public function markNotificationsRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Add a saved item to a list: the one chosen in the picker, otherwise the list it
     * was saved from, otherwise the user's only list.
     */
    public function readd(int $savedItemId, ?int $listId = null): void
    {
        $user = auth()->user();
        $saved = $user->savedItems()->findOrFail($savedItemId);

        $list = $listId !== null
            ? $user->shoppingLists()->whereKey($listId)->first()
            : ($saved->shopping_list_id !== null ? $user->shoppingLists()->whereKey($saved->shopping_list_id)->first() : null);

        if ($list === null && $listId === null && $saved->shopping_list_id === null && $user->shoppingLists()->count() === 1) {
            $list = $user->shoppingLists()->first();
        }

        if ($list === null) {
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
            ->orderByRaw('shopping_list_user.position is not null')
            ->orderBy('shopping_list_user.position')
            ->latest('shopping_lists.created_at')
            ->get();

        return view('livewire.lists.index', [
            'notifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
            'lists' => $lists,
            'urgentTotal' => (int) $lists->sum('urgent_count'),
            'pinned' => SavedItem::query()->where('user_id', $user->id)->orderBy('name')->limit(60)->get(),
            'greeting' => match (true) {
                now()->hour < 12 => 'დილა მშვიდობისა',
                now()->hour < 18 => 'დღე მშვიდობისა',
                default => 'საღამო მშვიდობისა',
            },
        ]);
    }
}
