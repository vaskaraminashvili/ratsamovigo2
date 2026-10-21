<div wire:poll.visible.10s x-data="{ sheet: false, pickFor: null }"
     x-on:list-created.window="sheet = false">
    <main class="px-5">
        <section class="pt-4">
            <h1 class="text-[2rem] font-extrabold leading-[1.15]">{{ $greeting }}, {{ auth()->user()->name }}</h1>
            @if ($urgentTotal > 0)
                <p class="mt-2 text-ink-300">შენს სიებში <span class="font-semibold text-urgent-fg">{{ $urgentTotal }} სასწრაფო ნივთია</span>.</p>
            @endif
        </section>

        @if ($notifications->isNotEmpty())
            <section class="mt-6 space-y-2" aria-label="შეტყობინებები">
                @foreach ($notifications as $notification)
                    <a href="{{ route('lists.show', $notification->data['list_id']) }}" wire:navigate wire:key="notification-{{ $notification->id }}"
                       class="block rounded-2xl border border-primary-line/50 bg-primary-400/10 p-3.5">
                        <span class="block text-[13px] text-ink-300">{{ $notification->data['from_name'] }} · {{ $notification->data['list_name'] }}</span>
                        <span class="mt-0.5 block font-semibold">{{ $notification->data['message'] }}</span>
                    </a>
                @endforeach
                <button type="button" wire:click="markNotificationsRead" class="rounded-full px-3 py-2 text-[14px] font-medium text-ink-300 hover:bg-ink-800">წაკითხულია</button>
            </section>
        @endif

        @if ($pinned->isNotEmpty())
            <section class="mt-8" aria-labelledby="pinned-h">
                <h2 id="pinned-h" class="flex items-center gap-2 whitespace-nowrap text-[17px] font-bold">
                    <svg class="size-4 shrink-0 text-primary-fg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"/></svg>
                    შენახული
                </h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($pinned as $item)
                        <button type="button" wire:key="pinned-{{ $item->id }}" @if ($item->shopping_list_id !== null || $lists->count() === 1) wire:click="readd({{ $item->id }})" @else @click="pickFor = pickFor === {{ $item->id }} ? null : {{ $item->id }}" @endif
                                class="flex shrink-0 items-center gap-2 rounded-full border border-ink-700 bg-ink-900 px-3.5 py-2 text-[15px] transition hover:border-ink-500">
                            <svg class="size-4 shrink-0 text-primary-fg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span class="font-medium">{{ $item->name }}</span>
                        </button>
                    @endforeach
                </div>

                <div x-show="pickFor !== null" x-cloak class="mt-3 rounded-2xl border border-ink-700 bg-ink-900 p-3">
                    <p class="text-[14px] text-ink-300">რომელ სიაში დავამატო?</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($lists as $targetList)
                            <button type="button" wire:key="pick-{{ $targetList->id }}" @click="$wire.readd(pickFor, {{ $targetList->id }}); pickFor = null"
                                    class="min-h-11 rounded-full border border-ink-700 px-4 text-[15px] font-medium hover:border-primary-line">{{ $targetList->name }}</button>
                        @endforeach
                        <button type="button" @click="pickFor = null" class="min-h-11 rounded-full px-4 text-[14px] text-ink-400">გაუქმება</button>
                    </div>
                </div>
            </section>
        @endif

        <section class="mt-10" aria-labelledby="lists-h">
            <h2 id="lists-h" class="text-lg font-bold">ჩემი სიები</h2>

            @if ($lists->isNotEmpty())
            <ul class="mt-2 divide-y divide-ink-800" wire:sort="sortList">
            @foreach ($lists as $list)
                @php
                    $total = $list->left_count + $list->done_count;
                    $progress = $total > 0 ? round($list->done_count / $total * 100) : 0;
                    $tints = ['bg-primary-400/15 text-primary-300', 'bg-urgent-400/15 text-urgent-300', 'bg-cart-400/15 text-cart-fg'];
                @endphp
                <li wire:key="list-{{ $list->id }}" wire:sort:item="{{ $list->id }}" class="flex items-center">
                    <a href="{{ route('lists.show', $list) }}" wire:navigate class="group flex min-w-0 flex-1 items-center gap-4 py-4">
                        <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl text-xl font-bold {{ $tints[$list->id % 3] }}">
                            @if ($list->coverUrl())
                                <img src="{{ $list->coverUrl() }}" alt="" class="size-full object-cover" loading="lazy">
                            @else
                                {{ mb_substr($list->name, 0, 1) }}
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[19px] font-bold leading-tight">{{ $list->name }}</p>
                            <p class="mt-1 text-[14px] text-ink-400">
                                {{ $list->left_count }} დარჩა@if ($list->urgent_count > 0), <span class="font-semibold text-urgent-fg">{{ $list->urgent_count }} სასწრაფო</span>@endif
                                @if ($list->bought_behavior === \App\Enums\BoughtBehavior::Hide)
                                    <span class="ml-2 rounded-md bg-ink-800 px-1.5 py-0.5 text-[12px] text-ink-300">ყოველდღიური</span>
                                @endif
                            </p>
                            <div class="mt-3 h-1 overflow-hidden rounded-full bg-ink-800" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="h-full rounded-full bg-cart-400" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        <div class="flex -space-x-2">
                            @foreach ($list->members->take(3) as $member)
                                <span class="grid size-8 place-items-center rounded-full text-[13px] font-bold text-on ring-2 ring-ink-950 {{ $member->avatarColorClass() }}" title="{{ $member->name }}">{{ $member->initials() }}</span>
                            @endforeach
                        </div>
                    </a>
                    <span wire:sort:handle class="grid size-11 shrink-0 cursor-grab touch-none place-items-center rounded-full text-ink-500 hover:bg-ink-800" aria-label="გადასატანად" title="გადატანა">
                        <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/></svg>
                    </span>
                </li>
            @endforeach
            </ul>
            @else
                <div class="mt-6 rounded-3xl border border-dashed border-ink-700 p-6 text-center">
                    <p class="text-lg font-semibold">ჯერ სია არ გაქვს</p>
                    <p class="mt-1 text-ink-400">შექმენი და მოსაწვევი ბმული გაუგზავნე მათ, ვისთვისაც ყიდულობ.</p>
                    <button type="button" @click="sheet = true; $nextTick(() => $refs.listName.focus())" class="btn-primary mt-5">სიის შექმნა</button>
                </div>
            @endif
        </section>
    </main>

    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-20 mx-auto max-w-md bg-gradient-to-t from-ink-950 via-ink-950/90 to-transparent px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-10">
        <button type="button" @click="sheet = true; $nextTick(() => $refs.listName.focus())"
                class="btn-primary pointer-events-auto w-full shadow-[0_12px_30px_-10px_color-mix(in_oklab,var(--p400)_45%,transparent)]">
            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            ახალი სია
        </button>
    </div>

    {{-- New list slide-up sheet --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-40" @keydown.escape.window="sheet = false">
        <div x-show="sheet" x-transition.opacity.duration.300ms class="absolute inset-0 bg-black/60" @click="sheet = false"></div>
        <section x-show="sheet" x-trap.noscroll="sheet" role="dialog" aria-modal="true" aria-labelledby="new-h"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                 class="absolute inset-x-0 bottom-0 mx-auto max-w-md rounded-t-[28px] border-t border-ink-700 bg-ink-900 px-5 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
            <div class="mx-auto mt-3 h-1.5 w-10 rounded-full bg-ink-700"></div>
            <h2 id="new-h" class="mt-5 text-2xl font-bold">ახალი სია</h2>

            <form wire:submit="createList" class="mt-5 space-y-5" novalidate>
                <x-field label="სიის სახელი" name="name" hint="შექმნის შემდეგ მიიღებ მოსაწვევ ბმულს." placeholder="სახლის საყიდლები" x-ref="listName" />

                <fieldset>
                    <legend class="field-label">ნაყიდი ნივთები</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['strike' => ['იხაზება', 'ერთჯერადი სიისთვის'], 'hide' => ['იმალება', 'ყოველდღიური სიისთვის']] as $value => [$title, $hint])
                            <label class="cursor-pointer rounded-2xl border p-3 transition {{ $boughtBehavior === $value ? 'border-primary-line bg-primary-400/10' : 'border-ink-700' }}">
                                <input type="radio" wire:model.live="boughtBehavior" value="{{ $value }}" class="sr-only">
                                <span class="block font-semibold">{{ $title }}</span>
                                <span class="block text-[13px] text-ink-400">{{ $hint }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="createList">სიის შექმნა</button>
            </form>
        </section>
    </div>
</div>
