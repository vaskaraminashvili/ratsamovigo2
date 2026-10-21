<?php

namespace App\Livewire\Lists;

use App\Enums\BoughtBehavior;
use App\Enums\Priority;
use App\Enums\Unit;
use App\Livewire\Concerns\ThrottlesActions;
use App\Livewire\Concerns\ValidatesIngredients;
use App\Models\ListItem;
use App\Models\PollVote;
use App\Models\SavedItem;
use App\Models\ShoppingList;
use App\Models\User;
use App\Notifications\GoingToStoreNotification;
use App\Support\CoverImage;
use App\Support\ProductSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::list')]
class Show extends Component
{
    use ThrottlesActions;
    use ValidatesIngredients;
    use WithFileUploads;

    public ShoppingList $shoppingList;

    public ?TemporaryUploadedFile $cover = null;

    public function updatedCover(): void
    {
        Gate::authorize('update', $this->shoppingList);

        $this->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'cover.image' => 'ატვირთე სურათი.',
            'cover.mimes' => 'დაშვებულია JPG, PNG ან WebP.',
            'cover.max' => 'სურათი 2 მბ-ზე მცირე უნდა იყოს.',
            'cover.uploaded' => 'სურათი 2 მბ-ზე მცირე უნდა იყოს.',
        ]);

        $newPath = CoverImage::store($this->cover);
        $oldPath = $this->shoppingList->cover_path;

        $this->shoppingList->update(['cover_path' => $newPath]);
        CoverImage::delete($oldPath);

        $this->reset('cover');
        $this->dispatch('toast', message: 'ფოტო შენახულია');
    }

    public function removeCover(): void
    {
        Gate::authorize('update', $this->shoppingList);

        CoverImage::delete($this->shoppingList->cover_path);
        $this->shoppingList->update(['cover_path' => null]);
    }

    public function mount(ShoppingList $shoppingList): void
    {
        $this->shoppingList = $shoppingList;

        Gate::authorize('view', $shoppingList);

        auth()->user()->unreadNotifications()
            ->where('data->list_id', $shoppingList->id)
            ->update(['read_at' => now()]);
    }

    /**
     * Product and price suggestions for the item being typed. Empty unless the list has
     * "fetch best price" turned on.
     *
     * @return list<array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}>
     */
    #[Renderless]
    public function searchProducts(string $query, ProductSearch $search): array
    {
        Gate::authorize('view', $this->shoppingList);

        if (! $this->shoppingList->fresh()->fetch_best_price) {
            return [];
        }

        $this->throttle('search-products', 60);

        return $search->suggestions($query);
    }

    /**
     * @param  array<string, mixed>|null  $catalog  Product picked from the price suggestions.
     */
    public function addItem(string $name, ?float $quantity, string $unit, bool $urgent, ?string $priority = null, ?array $catalog = null): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('add-item', 60);

        $validated = validator(
            ['name' => trim($name), 'quantity' => $quantity, 'unit' => $unit, 'priority' => $priority, 'catalog' => $catalog],
            [
                'name' => ['required', 'string', 'max:120'],
                'quantity' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
                'unit' => [Rule::enum(Unit::class)],
                'priority' => ['nullable', Rule::enum(Priority::class)],
                'catalog' => ['nullable', 'array'],
                'catalog.product_id' => ['nullable', 'integer', 'min:0'],
                'catalog.store_name' => ['required_with:catalog', 'string', 'max:80'],
                'catalog.store_slug' => ['nullable', 'string', 'max:40'],
                'catalog.price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                'catalog.unit' => ['nullable', 'string', 'max:40'],
                'catalog.url' => ['nullable', 'url:https', 'max:255'],
            ],
        )->validate();

        $level = $validated['priority'] !== null ? Priority::from($validated['priority']) : ($urgent ? Priority::Urgent : Priority::Normal);
        $picked = $validated['catalog'] ?? null;

        $this->shoppingList->items()->create([
            'added_by' => auth()->id(),
            'name' => $validated['name'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'priority' => $level,
            ...($picked === null ? [] : [
                'catalog_product_id' => ($picked['product_id'] ?? 0) > 0 ? $picked['product_id'] : null,
                'catalog_store_name' => $picked['store_name'],
                'catalog_store_slug' => $picked['store_slug'] ?? null,
                'catalog_price' => $picked['price'] ?? null,
                'catalog_unit' => $picked['unit'] ?? null,
                'catalog_url' => $picked['url'] ?? null,
            ]),
        ]);

        if ($level === Priority::Urgent) {
            $this->shoppingList->notifyMembers(auth()->user(), 'დაამატა სასწრაფო: '.$validated['name']);
        }
    }

    /**
     * @param  list<int|string>  $savedItemIds
     */
    public function addSavedItems(array $savedItemIds): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('add-saved', 30);

        $ids = array_slice(array_values(array_unique(array_map('intval', $savedItemIds))), 0, 50);

        $saved = auth()->user()->savedItems()->whereIn('id', $ids)->get();

        foreach ($saved as $savedItem) {
            $this->shoppingList->items()->create([
                'added_by' => auth()->id(),
                'name' => $savedItem->name,
                'quantity' => $savedItem->quantity,
                'unit' => Unit::tryFrom((string) $savedItem->unit) ?? Unit::Piece,
            ]);
        }

        if ($saved->isNotEmpty()) {
            $this->dispatch('toast', message: $saved->count() === 1 ? "{$saved->first()->name} დაემატა" : "{$saved->count()} ნივთი დაემატა");
        }
    }

    /**
     * @param  array<int, mixed>  $ingredients
     */
    public function addDish(string $name, array $ingredients, bool $remember = false): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('add-dish', 20);

        $dishName = validator(['name' => trim($name)], [
            'name' => ['required', 'string', 'max:120'],
        ], ['name.required' => 'დაწერე კერძის სახელი.'])->validate()['name'];

        $rows = $this->validatedIngredients($ingredients);

        foreach ($rows as $row) {
            $this->createDishItem($dishName, $row['name'], $row['quantity'], $row['unit']);
        }

        if ($remember) {
            $dish = auth()->user()->dishes()->firstOrCreate(['name' => $dishName]);
            $dish->ingredients()->delete();
            $dish->ingredients()->createMany(array_map(fn (array $row) => [
                'name' => $row['name'],
                'quantity' => $row['quantity'],
                'unit' => $row['quantity'] === null ? null : $row['unit'],
            ], $rows));
        }

        $this->dispatch('toast', message: "„{$dishName}“ დაემატა");
    }

    /**
     * @param  list<int|string>  $ingredientIds
     */
    public function addDishIngredients(int $dishId, array $ingredientIds): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('add-dish', 20);

        $dish = auth()->user()->dishes()->findOrFail($dishId);
        $ids = array_map('intval', $ingredientIds);

        $ingredients = $dish->ingredients()->whereIn('id', $ids)->get();

        foreach ($ingredients as $ingredient) {
            $this->createDishItem($dish->name, $ingredient->name, $ingredient->quantity, $ingredient->unit ?? Unit::Piece->value);
        }

        if ($ingredients->isNotEmpty()) {
            $this->dispatch('toast', message: "„{$dish->name}“ დაემატა");
        }
    }

    /**
     * @param  array<int, mixed>  $options
     */
    public function createPoll(string $question, array $options): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('create-poll', 10);

        $labels = array_values(array_filter(
            array_map(fn ($option) => is_string($option) ? trim($option) : '', array_slice($options, 0, 12)),
            fn (string $label) => $label !== '',
        ));

        $validated = validator(
            ['question' => trim($question), 'options' => $labels],
            [
                'question' => ['required', 'string', 'max:160'],
                'options' => ['required', 'array', 'min:2', 'max:6'],
                'options.*' => ['string', 'max:80', 'distinct:ignore_case'],
            ],
            [
                'question.required' => 'დაწერე კითხვა.',
                'options.min' => 'დაამატე მინიმუმ ორი ვარიანტი.',
                'options.max' => 'ვარიანტი 6-ზე მეტი არ შეიძლება.',
                'options.*.distinct' => 'ვარიანტები განსხვავებული უნდა იყოს.',
            ],
        )->validate();

        $poll = $this->shoppingList->polls()->create([
            'created_by' => auth()->id(),
            'question' => $validated['question'],
        ]);

        $poll->options()->createMany(array_map(fn (string $label) => ['label' => $label], $validated['options']));

        $this->shoppingList->notifyMembers(auth()->user(), 'შექმნა გამოკითხვა: '.$validated['question']);
    }

    public function vote(int $pollId, int $optionId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('vote', 60);

        $poll = $this->shoppingList->polls()->findOrFail($pollId);
        $option = $poll->options()->findOrFail($optionId);

        $poll->votes()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['poll_option_id' => $option->id],
        );
    }

    public function deletePoll(int $pollId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->shoppingList->polls()->where('created_by', auth()->id())->findOrFail($pollId)->delete();
    }

    /**
     * Turn a leading option into a list item and close the poll.
     */
    public function promotePoll(int $pollId, int $optionId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $poll = $this->shoppingList->polls()->with('options')->findOrFail($pollId);
        $option = $poll->options->firstWhere('id', $optionId) ?? abort(404);

        $counts = $poll->votes()->selectRaw('poll_option_id, count(*) as total')->groupBy('poll_option_id')->pluck('total', 'poll_option_id');
        $top = (int) $counts->max();

        if ($top === 0 || (int) ($counts[$option->id] ?? 0) !== $top) {
            $this->dispatch('toast', message: 'დაამატე მხოლოდ ლიდერი ვარიანტი');

            return;
        }

        $this->shoppingList->items()->create([
            'added_by' => auth()->id(),
            'name' => $option->label,
            'unit' => Unit::Piece,
        ]);

        $poll->delete();
        $this->dispatch('toast', message: "{$option->label} დაემატა სიაში");
    }

    /**
     * Tell other members you are heading to the store.
     *
     * @param  list<int|string>  $recipientIds
     */
    public function startShopping(array $recipientIds, string $message, bool $remember = false): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->throttle('notify', 5);

        $validated = validator(
            ['message' => trim($message), 'recipients' => array_values(array_unique(array_map('intval', $recipientIds)))],
            [
                'message' => ['required', 'string', 'max:160'],
                'recipients' => ['required', 'array', 'min:1', 'max:50'],
            ],
            [
                'message.required' => 'დაწერე შეტყობინება.',
                'message.max' => 'შეტყობინება 160 სიმბოლოზე გრძელი არ შეიძლება.',
                'recipients.required' => 'აირჩიე ვინმე, ვისაც შეატყობინებ.',
                'recipients.min' => 'აირჩიე ვინმე, ვისაც შეატყობინებ.',
            ],
        )->validate();

        $memberIds = $this->shoppingList->members()->pluck('users.id')->all();
        $recipientIds = array_values(array_diff($validated['recipients'], [auth()->id()]));

        if (array_diff($recipientIds, $memberIds) !== [] || $recipientIds === []) {
            throw ValidationException::withMessages(['recipients' => 'აირჩიე სიის სხვა წევრები.']);
        }

        $recipients = User::query()->whereIn('id', $recipientIds)->get();
        $notification = new GoingToStoreNotification($this->shoppingList, auth()->user(), $validated['message']);

        Notification::sendNow($recipients, $notification, ['database']);
        Notification::send($recipients, $notification);

        $this->shoppingList->update(['shopping_by' => auth()->id(), 'shopping_started_at' => now()]);

        if ($remember) {
            auth()->user()->update(['going_message' => $validated['message']]);
        }

        $this->dispatch('toast', message: 'შეტყობინება გაიგზავნა');
    }

    public function finishShopping(): void
    {
        Gate::authorize('view', $this->shoppingList);

        if ($this->shoppingList->shopping_by === null) {
            return;
        }

        if ($this->shoppingList->shopping_by !== auth()->id() && $this->shoppingList->owner_id !== auth()->id()) {
            abort(403);
        }

        $this->shoppingList->update(['shopping_by' => null, 'shopping_started_at' => null]);
    }

    public function toggleGroup(string $group): void
    {
        Gate::authorize('view', $this->shoppingList);

        $items = $this->shoppingList->items()->where('group_name', $group);

        if ((clone $items)->whereNull('bought_at')->exists()) {
            $items->whereNull('bought_at')->update(['bought_at' => now(), 'bought_by' => auth()->id(), 'claimed_by' => null]);
        } else {
            $items->update(['bought_at' => null, 'bought_by' => null]);
        }
    }

    public function deleteGroup(string $group): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->shoppingList->items()->where('group_name', $group)->delete();
    }

    protected function createDishItem(string $group, string $name, ?float $quantity, string $unit): void
    {
        $this->shoppingList->items()->create([
            'added_by' => auth()->id(),
            'name' => $name,
            'group_name' => $group,
            'quantity' => $quantity,
            'unit' => Unit::tryFrom($unit) ?? Unit::Piece,
        ]);
    }

    public function saveItem(int $itemId, string $name, ?float $quantity, string $unit, string $priority, ?bool $saved = null): void
    {
        Gate::authorize('view', $this->shoppingList);

        $validated = validator(
            ['name' => trim($name), 'quantity' => $quantity, 'unit' => $unit, 'priority' => $priority],
            [
                'name' => ['required', 'string', 'max:120'],
                'quantity' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
                'unit' => [Rule::enum(Unit::class)],
                'priority' => [Rule::enum(Priority::class)],
            ],
        )->validate();

        $item = $this->findItem($itemId);
        $item->update($validated);

        if ($saved !== null) {
            $this->syncSaved($item->refresh(), $saved);
        }
    }

    protected function syncSaved(ListItem $item, bool $saved): void
    {
        $user = auth()->user();
        $key = SavedItem::keyFor($item->name);

        if (! $saved) {
            $user->savedItems()->where('name_key', $key)->delete();

            return;
        }

        $user->savedItems()->updateOrCreate(
            ['name_key' => $key],
            [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->quantity === null ? null : $item->unit->value,
                'shopping_list_id' => $this->shoppingList->id,
            ],
        );
    }

    public function toggleBought(int $itemId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $item = $this->findItem($itemId);

        $item->update($item->isBought()
            ? ['bought_at' => null, 'bought_by' => null]
            : ['bought_at' => now(), 'bought_by' => auth()->id(), 'claimed_by' => null]);
    }

    public function toggleClaim(int $itemId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $item = $this->findItem($itemId);

        if ($item->isBought()) {
            return;
        }

        if ($item->claimed_by === null) {
            $item->update(['claimed_by' => auth()->id()]);
        } elseif ($item->claimed_by === auth()->id()) {
            $item->update(['claimed_by' => null]);
        }
    }

    public function deleteItem(int $itemId): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->findItem($itemId)->delete();
    }

    public function clearBought(): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->shoppingList->items()->whereNotNull('bought_at')->delete();
        $this->dispatch('toast', message: 'ნაყიდი ნივთები წაიშალა');
    }

    public function reopenBought(): void
    {
        Gate::authorize('view', $this->shoppingList);

        $this->shoppingList->items()->whereNotNull('bought_at')->update(['bought_at' => null, 'bought_by' => null]);
        $this->dispatch('toast', message: 'ნივთები სიაში დაბრუნდა');
    }

    public function saveSettings(string $name, bool $groupByPriority, string $boughtBehavior, ?bool $fetchBestPrice = null): void
    {
        Gate::authorize('update', $this->shoppingList);

        $validated = validator(
            ['name' => trim($name), 'boughtBehavior' => $boughtBehavior],
            [
                'name' => ['required', 'string', 'max:120'],
                'boughtBehavior' => [Rule::enum(BoughtBehavior::class)],
            ],
        )->validate();

        $this->shoppingList->update([
            'name' => $validated['name'],
            'bought_behavior' => $validated['boughtBehavior'],
            'group_by_priority' => $groupByPriority,
            'fetch_best_price' => $fetchBestPrice ?? $this->shoppingList->fetch_best_price,
        ]);

        $this->dispatch('toast', message: 'პარამეტრები შენახულია');
    }

    public function deleteList(): void
    {
        Gate::authorize('update', $this->shoppingList);

        $name = $this->shoppingList->name;
        $this->shoppingList->delete();

        session()->flash('status', "სია „{$name}“ წაიშალა.");
        $this->redirect(route('lists.index'), navigate: true);
    }

    public function removeMember(int $userId): void
    {
        Gate::authorize('update', $this->shoppingList);

        $member = $this->shoppingList->members()->whereKey($userId)->firstOrFail();

        if ($member->id === $this->shoppingList->owner_id) {
            return;
        }

        $this->releaseMember($member);
        $this->dispatch('toast', message: "{$member->name} ამოღებულია სიიდან");
    }

    public function transferOwnership(int $userId): void
    {
        Gate::authorize('update', $this->shoppingList);

        $member = $this->shoppingList->members()->whereKey($userId)->firstOrFail();

        if ($member->id === $this->shoppingList->owner_id) {
            return;
        }

        $this->shoppingList->update(['owner_id' => $member->id]);
        $this->dispatch('toast', message: "{$member->name} ახლა სიის მფლობელია");
    }

    public function regenerateInvite(): void
    {
        Gate::authorize('update', $this->shoppingList);

        $this->shoppingList->update(['invite_token' => Str::random(32)]);
        $this->dispatch('toast', message: 'ახალი ბმული შეიქმნა. ძველი აღარ მუშაობს.');
    }

    /**
     * Take a member off the list, releasing everything that still pointed at them.
     */
    protected function releaseMember(User $member): void
    {
        $this->shoppingList->items()->where('claimed_by', $member->id)->update(['claimed_by' => null]);
        PollVote::query()->whereIn('poll_id', $this->shoppingList->polls()->select('id'))->where('user_id', $member->id)->delete();

        if ($this->shoppingList->shopping_by === $member->id) {
            $this->shoppingList->update(['shopping_by' => null, 'shopping_started_at' => null]);
        }

        $this->shoppingList->members()->detach($member->id);
    }

    public function leaveList(): void
    {
        Gate::authorize('view', $this->shoppingList);

        if ($this->shoppingList->owner_id === auth()->id()) {
            return;
        }

        $this->releaseMember(auth()->user());

        session()->flash('status', "დატოვე სია „{$this->shoppingList->name}“.");
        $this->redirect(route('lists.index'), navigate: true);
    }

    protected function findItem(int $itemId): ListItem
    {
        return $this->shoppingList->items()->findOrFail($itemId);
    }

    public function render(): View
    {
        $this->shoppingList->refresh();

        $items = $this->shoppingList->items()
            ->with(['adder', 'claimer', 'buyer'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $hide = $this->shoppingList->bought_behavior === BoughtBehavior::Hide;

        $dishGroups = $items->filter(fn (ListItem $item) => $item->group_name !== null)->groupBy('group_name');
        $plain = $items->filter(fn (ListItem $item) => $item->group_name === null);

        $open = $plain->reject->isBought();
        $bought = $plain->filter->isBought()->values();

        $savedItems = auth()->user()->savedItems()->orderBy('name')->get();

        return view('livewire.lists.show', [
            'shopper' => $this->shoppingList->shopping_by === null ? null : $this->shoppingList->shopper,
            'goingMessage' => auth()->user()->going_message,
            'polls' => $this->shoppingList->polls()->with(['creator', 'options.votes.user'])->latest()->get(),
            'dishGroups' => $dishGroups,
            'dishLeft' => $dishGroups->flatten()->reject->isBought()->count(),
            'dishes' => auth()->user()->dishes()->with('ingredients')->orderBy('name')->get(),
            'savedItems' => $savedItems,
            'savedKeys' => $savedItems->pluck('name_key')->all(),
            'members' => $this->shoppingList->members()->get(),
            'openItems' => $open->sortBy(fn (ListItem $item) => $item->priority->sortOrder())->values(),
            'boughtItems' => $bought,
            'hideBought' => $hide,
            'inviteUrl' => route('join.show', $this->shoppingList->invite_token),
            'isOwner' => $this->shoppingList->owner_id === auth()->id(),
            'units' => Unit::options(),
            'priorities' => Priority::cases(),
        ]);
    }
}
