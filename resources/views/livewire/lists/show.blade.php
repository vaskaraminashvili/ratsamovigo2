@php
    $multiMember = $members->count() > 1;
    $grouped = $shoppingList->group_by_priority;
    $leftCount = $openItems->count() + $dishLeft;
    $urgentCount = $openItems->where('priority', \App\Enums\Priority::Urgent)->count();
    $icon = fn (string $d) => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
@endphp

<div class="mx-auto max-w-md pb-44" wire:poll.visible.5s x-data="listPage({ units: @js($units) })" data-fetch-price="{{ $shoppingList->fetch_best_price ? '1' : '0' }}" @click.outside="addMenu = false">
    <header class="sticky top-0 z-20 bg-ink-950/85 px-3 pb-3 pt-[max(.75rem,env(safe-area-inset-top))] backdrop-blur">
        <div class="flex items-center gap-1">
            <a href="{{ route('lists.index') }}" wire:navigate class="grid size-11 shrink-0 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="ჩემს სიებზე დაბრუნება">
                <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <h1 class="min-w-0 flex-1 truncate text-[19px] font-extrabold leading-tight">{{ $shoppingList->name }}</h1>
            <button type="button" @click="sheet = 'share'" class="flex shrink-0 items-center rounded-full p-1 hover:bg-ink-800" aria-label="სიის გაზიარება და წევრები">
                <span class="flex -space-x-2">
                    @foreach ($members->take(2) as $member)
                        <span class="grid size-7 place-items-center rounded-full text-xs font-bold text-on ring-2 ring-ink-950 {{ $member->avatarColorClass() }}">{{ $member->initials() }}</span>
                    @endforeach
                    <span class="grid size-7 place-items-center rounded-full bg-ink-800 text-ink-200 ring-2 ring-ink-950">
                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                    </span>
                </span>
            </button>
            @if ($isOwner)
                <button type="button" @click="openSettings(@js($shoppingList->name), @js($grouped), @js($shoppingList->bought_behavior->value), @js($shoppingList->fetch_best_price))"
                        class="grid size-11 shrink-0 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="სიის პარამეტრები">
                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                </button>
            @endif
        </div>
        <div class="flex items-center justify-between pl-12">
            <p class="text-[13px] text-ink-400">{{ $leftCount }} დარჩა@if ($urgentCount > 0) · <span class="font-semibold text-urgent-fg">{{ $urgentCount }} სასწრაფო</span>@endif</p>
            <button type="button" @click="view = view === 'activity' ? 'list' : 'activity'" class="-my-2 rounded-full px-3 py-2.5 text-[13px] font-semibold text-ink-300 hover:bg-ink-800" :aria-pressed="(view === 'activity').toString()"
                    x-text="view === 'activity' ? 'სია' : 'აქტივობა'">აქტივობა</button>
        </div>
    </header>

    <main class="px-5">
        <section x-show="view === 'activity'" x-cloak aria-label="აქტივობა" class="mt-4">
            @if ($activity->isEmpty())
                <p class="py-10 text-center text-[15px] text-ink-400">ჯერ არაფერი მომხდარა.</p>
            @else
                <ul class="divide-y divide-ink-800 rounded-2xl border border-ink-700 bg-ink-900 px-4">
                    @foreach ($activity as $event)
                        <li class="flex items-start gap-3 py-3">
                            <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-full text-xs font-bold text-on {{ $event['user']->avatarColorClass() }}">{{ $event['user']->initials() }}</span>
                            <div class="min-w-0">
                                <p class="text-[15px] leading-snug"><span class="font-semibold">{{ $event['user']->id === auth()->id() ? 'შენ' : $event['user']->name }}</span> {{ $event['text'] }}</p>
                                <p class="mt-0.5 text-[13px] text-ink-400">{{ $event['at']->locale('ka')->diffForHumans() }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div x-show="view === 'list'">
        @if ($shoppingList->coverUrl())
            <img src="{{ $shoppingList->coverUrl() }}" alt="" class="mt-1 aspect-[16/7] w-full rounded-3xl object-cover" loading="lazy">
        @endif

        {{-- Going to the store --}}
        @if ($multiMember)
            @if ($shopper)
                <div class="mt-4 flex items-center gap-3 rounded-2xl border border-cart-400/50 bg-cart-400/10 p-3.5" role="status">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-cart-400 text-on">{!! $icon('m4.5 12.75 6 6 9-13.5') !!}</span>
                    <p class="min-w-0 flex-1 text-[15px] font-medium">
                        {{ $shopper->id === auth()->id() ? 'შეტყობინება გაიგზავნა · მაღაზიაში ხარ' : $shopper->name.' მაღაზიაშია' }}
                    </p>
                    @if ($shopper->id === auth()->id() || $isOwner)
                        <button type="button" wire:click="finishShopping" class="shrink-0 rounded-full bg-ink-100 px-4 py-2 text-[14px] font-semibold text-ink-950">დავასრულე</button>
                    @endif
                </div>
            @else
                <button type="button" @click="openGoing(@js($goingMessage), @js($members->pluck('id')->reject(fn ($id) => $id === auth()->id())->values()))" class="mt-3 flex min-h-12 w-full items-center gap-3 rounded-2xl border border-ink-700 bg-ink-900 px-3.5 py-2 text-left hover:border-ink-500">
                    <span class="text-primary-fg">{!! $icon('M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z') !!}</span>
                    <span class="min-w-0 flex-1 truncate font-semibold">მაღაზიაში მიდიხარ?</span>
                </button>
            @endif
        @endif

        {{-- Polls --}}
        @foreach ($polls as $poll)
            @php
                $totalVotes = $poll->options->sum(fn ($option) => $option->votes->count());
                $maxVotes = $poll->options->max(fn ($option) => $option->votes->count()) ?? 0;
                $myVote = $poll->options->first(fn ($option) => $option->votes->contains('user_id', auth()->id()));
                $leaders = $poll->options->filter(fn ($option) => $maxVotes > 0 && $option->votes->count() === $maxVotes);
            @endphp
            <section class="mt-3 rounded-2xl border border-ink-700 bg-ink-900" wire:key="poll-{{ $poll->id }}" aria-label="გამოკითხვა">
                <button type="button" @click="openPoll = openPoll === {{ $poll->id }} ? null : {{ $poll->id }}" :aria-expanded="(openPoll === {{ $poll->id }}).toString()"
                        class="flex w-full items-center gap-3 px-3.5 py-3 text-left">
                    <span class="shrink-0 text-primary-fg">
                        {!! $icon('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z') !!}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-bold">{{ $poll->question }}</span>
                        <span class="block truncate text-[13px] text-ink-400">
                            @if ($totalVotes === 0)
                                ჯერ ხმა არავის მიუცია
                            @else
                                {{ \App\Support\Ka::votes($totalVotes) }} · ლიდერობს: {{ $leaders->pluck('label')->implode(', ') }}
                            @endif
                        </span>
                    </span>
                </button>

                <div x-show="openPoll === {{ $poll->id }}" x-cloak class="space-y-2 border-t border-ink-800 p-4">
                    @foreach ($poll->options as $option)
                        @php
                            $count = $option->votes->count();
                            $percent = $totalVotes > 0 ? (int) round($count / $totalVotes * 100) : 0;
                            $mine = $myVote?->id === $option->id;
                        @endphp
                        <div wire:key="poll-option-{{ $option->id }}">
                            <button type="button" wire:click="vote({{ $poll->id }}, {{ $option->id }})" aria-pressed="{{ $mine ? 'true' : 'false' }}"
                                    @class(['relative block w-full overflow-hidden rounded-2xl border px-3.5 py-3 text-left', 'border-primary-line' => $mine, 'border-ink-700' => ! $mine])>
                                <span class="absolute inset-y-0 left-0 bg-primary-400/15" style="width: {{ $percent }}%" aria-hidden="true"></span>
                                <span class="relative flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate font-medium">{{ $option->label }}</span>
                                    <span class="flex -space-x-1.5">
                                        @foreach ($option->votes->take(4) as $vote)
                                            <span class="grid size-5 place-items-center rounded-full text-[10px] font-bold text-on ring-2 ring-ink-900 {{ $vote->user->avatarColorClass() }}" title="{{ $vote->user->name }}">{{ $vote->user->initials() }}</span>
                                        @endforeach
                                    </span>
                                    <span class="w-10 shrink-0 text-right text-[14px] font-semibold tabular-nums">{{ $percent }}%</span>
                                </span>
                            </button>
                            @if ($leaders->contains('id', $option->id))
                                <button type="button" wire:click="promotePoll({{ $poll->id }}, {{ $option->id }})" class="mt-1 rounded-full px-3 py-2.5 text-[13px] font-semibold text-primary-fg hover:bg-ink-800">სიაში დამატება</button>
                            @endif
                        </div>
                    @endforeach

                    @if ($poll->created_by === auth()->id())
                        <button type="button" wire:click="deletePoll({{ $poll->id }})" wire:confirm="გამოკითხვა წაიშალოს?" class="btn-danger mt-2 w-full">გამოკითხვის წაშლა</button>
                    @endif
                </div>
            </section>
        @endforeach

        {{-- Open items --}}
        @if ($grouped)
            @foreach ($priorities as $priority)
                @php
                    $groupItems = $openItems->where('priority', $priority);
                @endphp
                @continue($groupItems->isEmpty())
                <section class="mt-7" wire:key="group-{{ $priority->value }}">
                    <h2 class="flex items-baseline gap-2">
                        <span @class(['text-[17px] font-bold', 'text-urgent-fg' => $priority === \App\Enums\Priority::Urgent])>{{ $priority->label() }}</span>
                        @if ($priority->hint())
                            <span class="text-[13px] text-ink-400">{{ $priority->hint() }}</span>
                        @endif
                    </h2>
                    <ul class="mt-2.5 min-h-12 space-y-1.5" wire:sort="sortItem" wire:sort:group="items" wire:sort:group-id="{{ $priority->value }}">
                        @foreach ($groupItems as $item)
                            <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="$multiMember" wire:sort:item="{{ $item->id }}" />
                        @endforeach
                    </ul>
                </section>
            @endforeach
        @elseif ($openItems->isNotEmpty())
            <section class="mt-7">
                <ul class="space-y-1.5">
                    @foreach ($openItems as $item)
                        <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="$multiMember" :show-urgent-mark="true" />
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Dishes --}}
        @foreach ($dishGroups as $groupName => $groupItems)
            @php
                $groupDone = $groupItems->filter->isBought()->count();
                $groupTotal = $groupItems->count();
                $groupComplete = $groupDone === $groupTotal;
            @endphp
            <section class="mt-7" wire:key="dish-{{ md5($groupName) }}" aria-label="{{ $groupName }}">
                <div class="flex items-center gap-2">
                    <h2 class="min-w-0 flex-1 truncate text-[17px] font-bold">{{ $groupName }}</h2>
                    <span class="shrink-0 text-[13px] tabular-nums text-ink-400">{{ $groupDone }}/{{ $groupTotal }}</span>
                    <button type="button" wire:click="toggleGroup(@js($groupName))" class="shrink-0 rounded-full border border-ink-700 px-3 py-2.5 text-[13px] font-medium text-ink-200 hover:border-ink-500">
                        {{ $groupComplete ? 'მოხსნა' : 'ყველა ნაყიდია' }}
                    </button>
                    <button type="button" wire:click="deleteGroup(@js($groupName))" wire:confirm="წაიშალოს „{{ $groupName }}“ სიიდან?"
                            class="grid size-9 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-urgent-fg" aria-label="{{ $groupName }} სიიდან წაშლა">
                        {!! $icon('M6 18 18 6M6 6l12 12') !!}
                    </button>
                </div>
                <div class="mt-2 h-1 overflow-hidden rounded-full bg-ink-800" role="progressbar" aria-valuenow="{{ $groupDone }}" aria-valuemin="0" aria-valuemax="{{ $groupTotal }}">
                    <div class="h-full rounded-full bg-cart-400" style="width: {{ $groupTotal > 0 ? round($groupDone / $groupTotal * 100) : 0 }}%"></div>
                </div>
                <ul class="mt-2.5 space-y-1.5">
                    @foreach ($groupItems as $item)
                        <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="false" />
                    @endforeach
                </ul>
            </section>
        @endforeach

        @if ($leftCount === 0 && $dishGroups->isEmpty())
            <div class="mt-10 rounded-3xl border border-dashed border-ink-700 p-6 text-center">
                @if ($boughtItems->isEmpty())
                    <p class="text-lg font-semibold">სია ცარიელია</p>
                    <p class="mt-1 text-ink-300">დაამატე პირველი ნივთი ქვემოთ.</p>
                @else
                    <p class="text-lg font-semibold">ყველაფერი ნაყიდია</p>
                    <p class="mt-1 text-ink-300">{{ $hideBought ? 'შემდეგი საყიდლებისთვის დააჭირე „თავიდან“ ან დაამატე ახალი ნივთი.' : 'წაშალე ნაყიდები ან დაამატე ახალი ნივთი.' }}</p>
                @endif
            </div>
        @endif

        {{-- Bought items --}}
        @if ($boughtItems->isNotEmpty())
            @if ($hideBought)
                <section class="mt-8 rounded-3xl border border-ink-800 bg-ink-900 p-4">
                    <div class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">დამალულია {{ $boughtItems->count() }} ნაყიდი</p>
                            <p class="text-[13px] leading-snug text-ink-400">„თავიდან“ ყველას სიაში დააბრუნებს შემდეგი საყიდლებისთვის.</p>
                        </div>
                        <button type="button" wire:click="reopenBought" class="flex shrink-0 items-center gap-1.5 rounded-full bg-ink-100 px-4 py-2 text-[14px] font-semibold text-ink-950">თავიდან</button>
                    </div>
                    <button type="button" @click="showHidden = !showHidden" class="mt-3 flex items-center gap-1 text-[14px] font-medium text-ink-300" :aria-expanded="showHidden.toString()">
                        <span x-text="showHidden ? 'დამალვა' : 'ჩვენება'"></span>
                    </button>
                    <ul x-show="showHidden" x-cloak class="mt-3 space-y-1.5">
                        @foreach ($boughtItems as $item)
                            <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="false" />
                        @endforeach
                    </ul>
                </section>
            @else
                <section class="mt-8 border-t border-ink-800 pt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-[14px] text-ink-400">ნაყიდია {{ $boughtItems->count() }}</p>
                        <button type="button" wire:click="clearBought" wire:confirm="წაიშალოს ყველა ნაყიდი ნივთი?" class="rounded-full px-3 py-2 text-[14px] font-medium text-ink-300 hover:bg-ink-800">გასუფთავება</button>
                    </div>
                    <ul class="mt-3 space-y-1.5">
                        @foreach ($boughtItems as $item)
                            <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="false" />
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
        </div>
    </main>

    {{-- Quick add --}}
    <div x-show="view === 'list'" class="fixed inset-x-0 bottom-0 z-20 mx-auto max-w-md bg-ink-950 px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3">
        @if ($savedItems->isNotEmpty() || $boughtItems->isNotEmpty())
            <div class="-mx-4 mb-2 flex gap-2 overflow-x-auto px-4 [scrollbar-width:none]">
                @foreach ($boughtItems->unique(fn ($bought) => \App\Models\SavedItem::keyFor($bought->name))->take(15) as $bought)
                    <button type="button" x-show="matchesDraft(@js(\App\Models\SavedItem::keyFor($bought->name)))" x-cloak wire:key="again-{{ $bought->id }}"
                            @click="reopenNow({{ $bought->id }})"
                            class="flex shrink-0 items-center gap-1.5 rounded-full border border-dashed border-ink-600 bg-ink-850 px-3 py-2.5 text-[14px] font-medium">
                        {!! $icon('M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99') !!}{{ $bought->name }}
                    </button>
                @endforeach
                @foreach ($savedItems as $savedItem)
                    <button type="button" x-show="matchesDraft(@js($savedItem->name_key))" x-cloak wire:key="suggest-{{ $savedItem->id }}"
                            @click="addSavedNow({{ $savedItem->id }})"
                            class="flex shrink-0 items-center gap-1.5 rounded-full border border-ink-700 bg-ink-850 px-3 py-2.5 text-[14px] font-medium">
                        {!! $icon('M12 4.5v15m7.5-7.5h-15') !!}{{ $savedItem->name }}
                    </button>
                @endforeach
            </div>
        @endif

        <div x-show="addMenu" x-cloak x-transition.opacity class="mb-2 overflow-hidden rounded-2xl border border-ink-700 bg-ink-850 p-1 shadow-[0_20px_40px_-15px_var(--shade)]">
            <button type="button" @click="addMenu = false; sheet = 'saved'" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left hover:bg-ink-800">
                {!! $icon('M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z') !!}
                <span class="font-medium">შენახულიდან</span>
            </button>
            <button type="button" @click="addMenu = false; sheet = 'dish'" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left hover:bg-ink-800">
                {!! $icon('M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z') !!}
                <span class="font-medium">კერძი</span>
            </button>
            <button type="button" @click="addMenu = false; sheet = 'poll'" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left hover:bg-ink-800">
                {!! $icon('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z') !!}
                <span class="font-medium">გამოკითხვა</span>
            </button>
        </div>

        <div x-show="draft.name.trim()" x-cloak x-transition.opacity class="mb-2 rounded-3xl border border-ink-700 bg-ink-850 p-3 shadow-[0_20px_40px_-15px_var(--shade)]">
            <x-qty-picker target="draft" />

            <div class="mt-3 grid grid-cols-3 gap-2" role="group" aria-label="პრიორიტეტი">
                @foreach ($priorities as $priority)
                    <button type="button" @click="draft.priority = '{{ $priority->value }}'" :aria-pressed="(draft.priority === '{{ $priority->value }}').toString()"
                            class="min-h-11 rounded-2xl border px-2 text-[14px] font-semibold transition"
                            :class="draft.priority === '{{ $priority->value }}' ? 'border-primary-line bg-primary-400/10' : 'border-ink-700 text-ink-300'">{{ $priority->label() }}</button>
                @endforeach
            </div>
        </div>

        <div x-show="suggestions.length" x-cloak x-transition.opacity class="mb-2 max-h-64 overflow-y-auto rounded-2xl border border-ink-700 bg-ink-850 shadow-[0_20px_40px_-15px_var(--shade)]" role="listbox" aria-label="ფასების შემოთავაზებები">
            <template x-for="(row, index) in suggestions" :key="row.product_id + '-' + index">
                <button type="button" role="option" @click="pickSuggestion(row)" class="block min-h-12 w-full border-b border-ink-700 px-4 py-2.5 text-left last:border-b-0 hover:bg-ink-800">
                    <span class="block truncate text-[15px] font-medium" x-text="row.name"></span>
                    <span class="block truncate text-[13px] text-primary-300" x-text="row.caption"></span>
                </button>
            </template>
        </div>

        <form @submit.prevent="add()" class="flex items-center gap-1.5 rounded-full border border-ink-700 bg-ink-850 p-1.5 focus-within:border-primary-line">
            <label for="draft" class="sr-only">ნივთის დამატება</label>
            <input id="draft" x-model="draft.name" enterkeyhint="done" autocomplete="off" maxlength="120"
                   class="h-10 min-w-0 flex-1 bg-transparent pl-4 text-[16px] outline-none placeholder:text-ink-500"
                   :placeholder="draft.priority === 'urgent' ? 'სასწრაფო ნივთი' : 'დაამატე ნივთი'">
            <button type="button" @click.stop="addMenu = !addMenu" class="grid size-11 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800"
                    :aria-expanded="addMenu.toString()" aria-label="დამატების მენიუ" title="შენახულიდან, კერძი, გამოკითხვა">
                {!! $icon('M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z') !!}
            </button>
            <button type="button" @click="draft.priority = draft.priority === 'urgent' ? 'normal' : 'urgent'" class="grid size-11 shrink-0 place-items-center rounded-full transition"
                    :class="draft.priority === 'urgent' ? 'bg-urgent-400 text-on' : 'text-ink-400 hover:bg-ink-800'"
                    :aria-pressed="(draft.priority === 'urgent').toString()" aria-label="ახალი ნივთი სასწრაფოა">
                {!! $icon('m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z') !!}
            </button>
            <button class="grid size-11 shrink-0 place-items-center rounded-full transition" :class="draft.name.trim() ? 'bg-primary-400 text-on' : 'bg-ink-800 text-ink-500'" :disabled="!draft.name.trim()" aria-label="დამატება">
                {!! $icon('M12 4.5v15m7.5-7.5h-15') !!}
            </button>
        </form>
    </div>

    {{-- Edit item --}}
    <x-sheet name="item" label="ნივთის პარამეტრები">
        <form @submit.prevent="saveItem()" class="mt-5 space-y-5">
            <div>
                <label for="edit-name" class="field-label">ნივთის სახელი</label>
                <input id="edit-name" class="field" x-model="edit.name" maxlength="120" autocomplete="off">
                <p x-show="edit.meta" x-text="edit.meta" class="mt-1.5 text-[13px] text-ink-400"></p>
            </div>

            <x-qty-picker target="edit" />

            <fieldset x-show="!edit.group">
                <legend class="field-label">პრიორიტეტი</legend>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($priorities as $priority)
                        <label class="cursor-pointer rounded-2xl border p-3 text-center transition"
                               :class="edit.priority === '{{ $priority->value }}' ? 'border-primary-line bg-primary-400/10' : 'border-ink-700'">
                            <input type="radio" class="sr-only" value="{{ $priority->value }}" x-model="edit.priority">
                            <span class="block text-[14px] font-semibold">{{ $priority->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <label class="flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
                <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" x-model="edit.saved">
                <span>
                    <span class="block font-semibold">შენახვა</span>
                    <span class="block text-[13px] text-ink-400">შენახულებში გამოჩნდება და სწრაფად დაემატება.</span>
                </span>
            </label>

            <div class="flex gap-3">
                <button type="button" @click="deleteItem()" class="btn-danger shrink-0">წაშლა</button>
                <button type="submit" class="btn-primary flex-1" :disabled="!edit.name.trim()">შენახვა</button>
            </div>
        </form>
    </x-sheet>

    {{-- Going to the store --}}
    @if ($multiMember)
        <x-sheet name="going" label="მაღაზიაში მივდივარ">
            <h2 class="mt-5 text-2xl font-bold">მაღაზიაში მიდიხარ?</h2>
            <p class="mt-2 text-ink-300">აირჩიე ვის შეატყობინებ.</p>

            <form class="mt-5 space-y-5"
                  @submit.prevent="if (!going.message.trim() || !going.to.length) return; const g = going; sheet = null; $wire.startShopping(g.to, g.message.trim(), g.remember)">
                <ul class="space-y-1.5">
                    @foreach ($members->reject(fn ($member) => $member->id === auth()->id()) as $member)
                        <li wire:key="going-{{ $member->id }}">
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-ink-700 px-3.5 py-3" :class="going.to.includes({{ $member->id }}) ? 'border-primary-line bg-primary-400/10' : ''">
                                <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" :checked="going.to.includes({{ $member->id }})" @change="toggleGoing({{ $member->id }})">
                                <span class="grid size-8 place-items-center rounded-full text-[13px] font-bold text-on {{ $member->avatarColorClass() }}">{{ $member->initials() }}</span>
                                <span class="font-medium">{{ $member->name }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>

                <div>
                    <label for="going-message" class="field-label">შეტყობინება</label>
                    <textarea id="going-message" x-model="going.message" maxlength="160" rows="3" class="field !h-auto py-3"></textarea>
                    <p class="field-hint"><span x-text="going.message.length"></span>/160</p>
                    <div class="-mx-1 mt-2 flex gap-1.5 overflow-x-auto px-1 pb-0.5 [scrollbar-width:none]">
                        @foreach (['მაღაზიაში მივდივარ, რამე გინდათ?', 'გზაში ვარ, ბოლო წუთს ჩაამატეთ სიაში.', 'ახლა მაღაზიაში ვარ, მომწერეთ.'] as $preset)
                            <button type="button" @click="going.message = @js($preset)" class="shrink-0 rounded-full border border-ink-700 px-3.5 py-2.5 text-[14px] text-ink-200 hover:border-ink-500">{{ $preset }}</button>
                        @endforeach
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
                    <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" x-model="going.remember">
                    <span class="font-semibold">ტექსტის დამახსოვრება</span>
                </label>

                @error('recipients') <p class="field-error">{{ $message }}</p> @enderror
                @error('message') <p class="field-error">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary w-full disabled:opacity-40" :disabled="!going.message.trim() || !going.to.length">გაგზავნა</button>
            </form>
        </x-sheet>
    @endif

    {{-- New poll --}}
    <x-sheet name="poll" label="გამოკითხვა">
        <h2 class="mt-5 text-2xl font-bold">გამოკითხვა</h2>
        <p class="mt-2 text-ink-300">ჰკითხე ოჯახს, რა იყიდოთ.</p>
        <form class="mt-5 space-y-5" x-data="{ question: '', options: ['', ''], get ready() { return this.question.trim() !== '' && this.options.filter((o) => o.trim()).length >= 2; } }"
              @submit.prevent="if (!ready) return; const q = question.trim(), o = options.map((x) => x.trim()).filter(Boolean); sheet = null; question = ''; options = ['', '']; $wire.createPoll(q, o)">
            <div>
                <label for="poll-question" class="field-label">კითხვა</label>
                <input id="poll-question" class="field" x-model="question" maxlength="160" autocomplete="off" placeholder="რა ვივახშმოთ?">
            </div>
            <div>
                <p class="field-label">ვარიანტები</p>
                <ul class="space-y-2">
                    <template x-for="(option, index) in options" :key="index">
                        <li class="flex items-center gap-2">
                            <input class="field min-w-0 flex-1" x-model="options[index]" maxlength="80" autocomplete="off" :placeholder="'ვარიანტი ' + (index + 1)" :aria-label="'ვარიანტი ' + (index + 1)">
                            <button type="button" x-show="options.length > 2" @click="options.splice(index, 1)" class="grid size-11 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800" aria-label="ვარიანტის წაშლა">
                                {!! $icon('M6 18 18 6M6 6l12 12') !!}
                            </button>
                        </li>
                    </template>
                </ul>
                <button type="button" x-show="options.length < 6" @click="options.push('')" class="btn-ghost mt-3 w-full">+ ვარიანტის დამატება</button>
            </div>
            @error('options') <p class="field-error">{{ $message }}</p> @enderror
            <button type="submit" class="btn-primary w-full disabled:opacity-40" :disabled="!ready">გამოკითხვის შექმნა</button>
        </form>
    </x-sheet>

    {{-- Add a dish --}}
    <x-sheet name="dish" label="კერძის დამატება">
        <h2 class="mt-5 text-2xl font-bold">კერძი</h2>
        <p class="mt-2 text-ink-300">ინგრედიენტები ერთად დაჯგუფდება სიაში.</p>
        <form x-data="dishForm({ units })" class="mt-5"
              @submit.prevent="if (!ready) return; const n = name.trim(), p = payload(), r = remember; sheet = null; clear(); $wire.addDish(n, p, r)">
            <x-dish-form :remember="true">
                @error('ingredients') <p class="field-error">{{ $message }}</p> @enderror
                <button type="submit" class="btn-primary w-full disabled:opacity-40" :disabled="!ready">სიაში დამატება</button>
            </x-dish-form>
        </form>
    </x-sheet>

    {{-- Add from saved --}}
    <x-sheet name="saved" label="შენახულიდან დამატება">
        <h2 class="mt-5 text-2xl font-bold">შენახულიდან</h2>
        <div class="mt-4 grid grid-cols-2 rounded-full border border-ink-700 p-1 text-[15px] font-semibold" role="tablist">
            <button type="button" role="tab" @click="savedTab = 'items'" :aria-selected="(savedTab === 'items').toString()" class="rounded-full px-3 py-2 transition" :class="savedTab === 'items' ? 'bg-primary-400 text-on' : 'text-ink-300'">ნივთები</button>
            <button type="button" role="tab" @click="savedTab = 'dishes'" :aria-selected="(savedTab === 'dishes').toString()" class="rounded-full px-3 py-2 transition" :class="savedTab === 'dishes' ? 'bg-primary-400 text-on' : 'text-ink-300'">კერძები</button>
        </div>

        <div x-show="savedTab === 'dishes'" x-cloak>
            @if ($dishes->isEmpty())
                <p class="mt-4 text-ink-300">შენახული კერძი ჯერ არ გაქვს. „კერძი“ მენიუში ჩართე დამახსოვრება ან დაამატე პროფილში.</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($dishes as $dish)
                        <li wire:key="dish-pick-{{ $dish->id }}" class="rounded-2xl border border-ink-700">
                            <button type="button" @click="showDish({{ $dish->id }}, @js($dish->ingredients->pluck('id')->all()))" class="flex w-full items-center gap-3 px-3.5 py-3 text-left" :aria-expanded="(openDish === {{ $dish->id }}).toString()">
                                <span class="min-w-0 flex-1 truncate font-semibold">{{ $dish->name }}</span>
                                <span class="shrink-0 text-[13px] text-ink-400">{{ \App\Support\Ka::ingredients($dish->ingredients->count()) }}</span>
                            </button>
                            <ul x-show="openDish === {{ $dish->id }}" x-cloak class="space-y-1 border-t border-ink-800 px-3.5 py-2">
                                @foreach ($dish->ingredients as $ingredient)
                                    <li wire:key="dish-ing-{{ $ingredient->id }}">
                                        <label class="flex cursor-pointer items-center gap-3 py-1.5">
                                            <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" :checked="dishPick.includes({{ $ingredient->id }})" @change="toggleDishPick({{ $ingredient->id }})">
                                            <span class="min-w-0 flex-1 truncate">{{ $ingredient->name }}</span>
                                            @if ($ingredient->quantity !== null)
                                                <span class="shrink-0 rounded-md bg-ink-800 px-1.5 py-0.5 text-[13px] font-semibold tabular-nums">{{ str_replace('.', ',', (string) $ingredient->quantity) }} {{ $ingredient->unit }}</span>
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
                <button type="button" x-show="openDish !== null" x-cloak @click="addDishPicked()" :disabled="dishPick.length === 0" class="btn-primary mt-5 w-full disabled:opacity-40">
                    დამატება<span x-show="dishPick.length" x-text="' (' + dishPick.length + ')'"></span>
                </button>
            @endif
        </div>

        <div x-show="savedTab === 'items'">
        @if ($savedItems->isEmpty())
            <p class="mt-2 text-ink-300">შენახული ნივთები ჯერ არ გაქვს. ნივთის რედაქტირებისას ჩართე „შენახვა“ ან დაამატე პროფილში.</p>
            <a href="{{ route('settings.profile') }}" wire:navigate class="btn-ghost mt-5 w-full">პროფილში გახსნა</a>
        @else
            <p class="mt-2 text-ink-300">მონიშნე რაც გჭირდება და დაამატე ერთად.</p>
            <ul class="mt-4 space-y-1.5">
                @foreach ($savedItems as $savedItem)
                    <li wire:key="saved-{{ $savedItem->id }}">
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-ink-700 px-3.5 py-3" :class="pick.includes({{ $savedItem->id }}) ? 'border-primary-line bg-primary-400/10' : ''">
                            <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" :checked="pick.includes({{ $savedItem->id }})" @change="togglePick({{ $savedItem->id }})">
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $savedItem->name }}</span>
                            @if ($savedItem->quantity !== null)
                                <span class="shrink-0 rounded-md bg-ink-800 px-1.5 py-0.5 text-[13px] font-semibold tabular-nums">{{ str_replace('.', ',', (string) $savedItem->quantity) }} {{ $savedItem->unit }}</span>
                            @endif
                        </label>
                    </li>
                @endforeach
            </ul>
            <button type="button" @click="addPicked()" :disabled="pick.length === 0" class="btn-primary mt-5 w-full disabled:opacity-40">
                დამატება<span x-show="pick.length" x-text="' (' + pick.length + ')'"></span>
            </button>
        @endif
        </div>
    </x-sheet>

    {{-- Share + members --}}
    <x-sheet name="share" label="სიის გაზიარება">
        <h2 class="mt-5 text-2xl font-bold">გაზიარება</h2>
        <p class="mt-2 text-ink-300">გაუგზავნე ბმული ოჯახს. ვინც გახსნის, სიას შეუერთდება.</p>

        <div class="mt-4 flex items-center gap-2 rounded-2xl border border-ink-700 bg-ink-950 p-2 pl-4">
            <span class="min-w-0 flex-1 truncate text-[15px] text-ink-200">{{ $inviteUrl }}</span>
            <button type="button" @click="copyInvite(@js($inviteUrl))" class="btn-primary h-11 px-4" x-text="copied ? 'დაკოპირდა' : 'კოპირება'"></button>
        </div>

        @if ($isOwner)
            <button type="button" wire:click="regenerateInvite" wire:confirm="ძველი ბმული აღარ იმუშავებს. ახალი ბმული შეიქმნას?" class="mt-2 rounded-full px-3 py-2 text-[14px] font-medium text-ink-300 hover:bg-ink-800">ბმულის განახლება</button>
        @endif

        <h3 class="mt-7 text-[17px] font-bold">წევრები · {{ $members->count() }}</h3>
        <ul class="mt-2 divide-y divide-ink-800">
            @foreach ($members as $member)
                <li class="flex items-center gap-3 py-3">
                    <span class="grid size-10 place-items-center rounded-full font-bold text-on {{ $member->avatarColorClass() }}">{{ $member->initials() }}</span>
                    <span class="flex-1 font-medium">{{ $member->id === auth()->id() ? 'შენ' : $member->name }}</span>
                    <span class="text-[13px] text-ink-400">{{ $member->id === $shoppingList->owner_id ? 'მფლობელი' : 'წევრი' }}</span>
                    @if ($isOwner && $member->id !== $shoppingList->owner_id)
                        <button type="button" wire:click="transferOwnership({{ $member->id }})" wire:confirm="{{ $member->name }} გახდება სიის მფლობელი, შენ კი ჩვეულებრივი წევრი. გაგრძელება?"
                                class="grid size-9 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-primary-fg" aria-label="{{ $member->name }}-ის მფლობელად დანიშვნა" title="მფლობელად დანიშვნა">
                            {!! $icon('M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z') !!}
                        </button>
                        <button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="{{ $member->name }} ამოიღო სიიდან?"
                                class="grid size-9 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-urgent-fg" aria-label="{{ $member->name }}-ის ამოღება" title="ამოღება">
                            {!! $icon('M6 18 18 6M6 6l12 12') !!}
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>

        @unless ($isOwner)
            <button type="button" wire:click="leaveList" wire:confirm="დარწმუნებული ხარ, რომ სიის დატოვება გინდა?" class="btn-danger mt-4 w-full">სიის დატოვება</button>
        @endunless
    </x-sheet>

    {{-- Settings (owner) --}}
    @if ($isOwner)
        <x-sheet name="settings" label="სიის პარამეტრები">
            <h2 class="mt-5 text-2xl font-bold">სიის პარამეტრები</h2>

            <div class="mt-5">
                <p class="field-label">ფოტო</p>
                <div class="flex items-center gap-3">
                    <span class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-ink-800 text-ink-400">
                        @if ($shoppingList->coverUrl())
                            <img src="{{ $shoppingList->coverUrl() }}" alt="" class="size-full object-cover">
                        @else
                            {!! $icon('m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z') !!}
                        @endif
                    </span>
                    <div class="flex flex-1 flex-wrap gap-2">
                        <label class="btn-ghost h-11 cursor-pointer px-4 text-[14px]">
                            <span wire:loading.remove wire:target="cover">{{ $shoppingList->coverUrl() ? 'შეცვლა' : 'ატვირთვა' }}</span>
                            <span wire:loading wire:target="cover">იტვირთება…</span>
                            <input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        @if ($shoppingList->coverUrl())
                            <button type="button" wire:click="removeCover" class="btn-danger h-11 px-4 text-[14px]">წაშლა</button>
                        @endif
                    </div>
                </div>
                @error('cover') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <form @submit.prevent="saveSettings()" class="mt-5 space-y-5">
                <div>
                    <label for="settings-name" class="field-label">სიის სახელი</label>
                    <input id="settings-name" class="field" x-model="settings.name" maxlength="120">
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
                    <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" x-model="settings.group">
                    <span>
                        <span class="block font-semibold">დაჯგუფება პრიორიტეტით</span>
                        <span class="block text-[13px] text-ink-400">სასწრაფო, საყიდელი და „თუ ნახავ“ ცალ-ცალკე ჩანს.</span>
                    </span>
                </label>

                <fieldset>
                    <legend class="field-label">ნაყიდი ნივთები</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['strike' => ['იხაზება', 'ერთჯერადი სიისთვის'], 'hide' => ['იმალება', 'ყოველდღიური სიისთვის']] as $value => [$title, $hint])
                            <label class="cursor-pointer rounded-2xl border p-3 transition" :class="settings.behavior === '{{ $value }}' ? 'border-primary-line bg-primary-400/10' : 'border-ink-700'">
                                <input type="radio" class="sr-only" value="{{ $value }}" x-model="settings.behavior">
                                <span class="block font-semibold">{{ $title }}</span>
                                <span class="block text-[13px] text-ink-400">{{ $hint }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <label class="flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
                    <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" x-model="settings.fetchPrice">
                    <span>
                        <span class="block font-semibold">საუკეთესო ფასის ძებნა</span>
                        <span class="block text-[13px] text-ink-400">ნივთის სახელის აკრეფისას მაღაზიების ფასებს შემოგთავაზებს.</span>
                    </span>
                </label>

                <button type="submit" class="btn-primary w-full" :disabled="!settings.name.trim()">შენახვა</button>
                <button type="button" wire:click="deleteList" wire:confirm="სია სამუდამოდ წაიშლება ყველასთვის. გაგრძელება?" class="btn-danger w-full">სიის წაშლა</button>
            </form>
        </x-sheet>
    @endif
</div>
