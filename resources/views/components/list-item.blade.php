@props(['item', 'showUrgentMark' => false, 'multiMember' => false, 'savedKeys' => []])

@php
    $me = auth()->id();
    $done = $item->isBought();
    $isUrgent = $item->priority === \App\Enums\Priority::Urgent;
    $claimedByMe = $item->claimed_by === $me;
    $payload = [
        'id' => $item->id,
        'name' => $item->name,
        'qty' => $item->quantity,
        'unit' => $item->unit->value,
        'priority' => $item->priority->value,
        'group' => $item->group_name,
        'saved' => in_array(\App\Models\SavedItem::keyFor($item->name), $savedKeys, true),
        'meta' => implode(' · ', array_filter([
            $item->added_by === $me ? 'შენ დაამატე' : 'დაამატა '.($item->adder?->name ?? '—'),
            $done ? ($item->bought_by === $me ? 'შენ იყიდე' : 'იყიდა '.($item->buyer?->name ?? '—')) : null,
        ])),
    ];

    $check = '<svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';
    $circle = fn (bool $isDone) => '<span class="grid size-6 place-items-center rounded-full border-2 transition '
        .($isDone ? 'border-cart-400 bg-cart-400 text-on' : ($isUrgent ? 'border-urgent-400' : 'border-ink-400')).'">'
        .($isDone ? $check : '').'</span>';
@endphp

<li wire:key="item-{{ $item->id }}-{{ $done ? 1 : 0 }}-{{ $item->claimed_by ?? 0 }}"
    x-data="{ flip: false, mine: @js($claimedByMe),
        toggle() { this.mine = ! this.mine; $wire.toggleClaim({{ $item->id }}).catch(() => this.mine = ! this.mine) } }"
    {{ $attributes->class([
        'relative flex items-center gap-1 overflow-hidden rounded-2xl border pr-2 transition-colors',
        'border-dashed border-ink-600 bg-transparent' => $done,
        'border-primary-line/50 bg-ink-900' => ! $done && $claimedByMe,
        'border-ink-700 bg-ink-900' => ! $done && ! $claimedByMe,
    ]) }}>
    @if ($isUrgent && ! $done)
        <span class="absolute inset-y-3 left-0 w-1 rounded-r-full bg-urgent-400" aria-hidden="true"></span>
    @endif

    <button type="button" @click="flip = !flip; $wire.toggleBought({{ $item->id }}).catch(() => flip = false)" class="grid size-12 shrink-0 place-items-center"
            aria-label="{{ $item->name }}: {{ $done ? 'მონიშვნის მოხსნა' : 'ნაყიდად მონიშვნა' }}">
        <span x-show="!flip">{!! $circle($done) !!}</span>
        <span x-show="flip" x-cloak>{!! $circle(! $done) !!}</span>
    </button>

    <button type="button" @click="openItem(@js($payload))" class="flex min-w-0 flex-1 items-center gap-2 py-3 text-left">
        <span class="min-w-0 flex-1">
            <span class="flex items-center gap-1.5">
                @if ($showUrgentMark && $isUrgent && ! $done)
                    <span class="shrink-0 text-urgent-fg" title="სასწრაფო">
                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                    </span>
                @endif
                <span @class(['truncate text-[17px] font-medium', 'text-ink-400 line-through decoration-ink-500' => $done])
                      :class="{{ $done ? "{ '!no-underline !text-ink-100': flip }" : "{ 'line-through text-ink-400 decoration-ink-500': flip }" }}">{{ $item->name }}</span>
                @if ($payload['saved'])
                    <svg class="size-3.5 shrink-0 text-primary-fg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" role="img" aria-label="შენახულია"><path d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z"/></svg>
                @endif
                @if ($item->quantity !== null)
                    <span @class([
                        'shrink-0 rounded-md px-1.5 py-0.5 text-[13px] font-semibold tabular-nums',
                        'bg-ink-850 text-ink-400' => $done,
                        'bg-ink-800 text-ink-100' => ! $done,
                    ])>{{ $item->quantityLabel() }}</span>
                @endif
            </span>
            @if ($item->catalog_store_name)
                <span class="mt-1.5 flex min-w-0 items-center gap-2">
                    @if ($item->catalog_price !== null)
                        <span @class([
                            'shrink-0 rounded-lg px-2 py-0.5 text-[15px] font-extrabold tabular-nums',
                            'bg-ink-850 text-ink-400' => $done,
                            'bg-primary-400/15 text-primary-300' => ! $done,
                        ])>{{ number_format((float) $item->catalog_price, 2, '.', '') }} ₾</span>
                    @endif
                    <span @class(['min-w-0 truncate text-[13px]', 'text-ink-500' => $done, 'text-ink-300' => ! $done])>
                        იყიდე <span @class(['font-semibold', 'text-ink-100' => ! $done])>{{ $item->catalog_store_name }}</span>@if ($item->catalog_unit) · {{ $item->catalog_unit }}@endif
                    </span>
                </span>
            @endif
        </span>
    </button>

    @if (! $done && $multiMember)
        {{-- small circle on the right: empty "+" to claim, avatar when claimed (same for every member) --}}
        @if ($item->claimed_by && ! $claimedByMe)
            <span class="shrink-0 px-2" x-show="!flip" title="ყიდულობს: {{ $item->claimer->name }}">
                <x-avatar :user="$item->claimer" class="size-6 rounded-full text-[11px] font-bold text-on" />
                <span class="sr-only">ყიდულობს: {{ $item->claimer->name }}</span>
            </span>
        @else
            <button type="button" x-show="!flip" @click="toggle()" class="grid size-10 shrink-0 place-items-center"
                    :aria-label="mine ? 'აღარ ვიყიდი' : 'ვიყიდი'" :title="mine ? 'შენ ყიდულობ' : 'ვიყიდი'">
                <span x-show="!mine" @if ($claimedByMe) x-cloak @endif class="grid size-6 place-items-center rounded-full border border-dashed border-ink-500 text-ink-400">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </span>
                <span x-show="mine" @if (! $claimedByMe) x-cloak @endif>
                    <x-avatar :user="auth()->user()" class="size-6 rounded-full text-[11px] font-bold text-on" />
                </span>
            </button>
        @endif
    @endif
</li>
