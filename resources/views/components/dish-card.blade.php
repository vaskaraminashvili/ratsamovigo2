@props(['groupName', 'items', 'savedKeys' => [], 'multiMember' => false, 'sortable' => false, 'compact' => false, 'prep' => []])

@php
    $groupName = (string) $groupName;
    $spinHash = crc32($groupName.'spin');
    $groupDone = $items->filter->isBought()->count();
    $groupTotal = $items->count();
    $groupComplete = $groupDone === $groupTotal;
@endphp

<section @class(['dish-card glass-card', 'mt-3' => $compact, 'mt-7' => ! $compact]) data-glow="{{ crc32($groupName) % 6 }}" style="--spin-start: {{ intdiv($spinHash, 8) % 360 }}deg; --spin-dur: {{ 7 + $spinHash % 4 }}s" wire:key="dish-{{ md5($groupName) }}" aria-label="{{ $groupName }}">
    <div class="flex items-center gap-2">
        <h2 class="min-w-0 flex-1 break-words text-[17px] font-bold leading-snug">
            @if ($prep)
                <button type="button" @click="prep = { title: @js($groupName), steps: @js($prep) }; sheet = 'prep'" class="-my-1 flex min-h-11 w-full items-center gap-1.5 text-left font-bold hover:text-primary-400" aria-label="{{ $groupName }} — მომზადება">
                    <span class="min-w-0 break-words">{{ $groupName }}</span>
                    <svg class="size-4 shrink-0 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                </button>
            @else
                {{ $groupName }}
            @endif
        </h2>
        <span class="shrink-0 text-[13px] tabular-nums text-ink-400">{{ $groupDone }}/{{ $groupTotal }}</span>
        <button type="button" wire:click="toggleGroup(@js($groupName))" class="shrink-0 rounded-full border border-ink-700 px-3 py-2.5 text-[13px] font-medium text-ink-200 hover:border-ink-500">
            {{ $groupComplete ? 'მოხსნა' : 'ყველა ნაყიდია' }}
        </button>
        <button type="button" wire:click="deleteGroup(@js($groupName))" wire:confirm="წაიშალოს „{{ $groupName }}“ სიიდან?"
                class="grid size-9 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-urgent-fg" aria-label="{{ $groupName }} სიიდან წაშლა">
            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="mt-2 h-1 overflow-hidden rounded-full bg-ink-800" role="progressbar" aria-valuenow="{{ $groupDone }}" aria-valuemin="0" aria-valuemax="{{ $groupTotal }}">
        <div class="h-full rounded-full bg-cart-400" style="width: {{ $groupTotal > 0 ? round($groupDone / $groupTotal * 100) : 0 }}%"></div>
    </div>
    <ul class="mt-2.5 space-y-1.5" @if ($sortable) wire:sort="sortDishItem" @endif>
        @foreach ($items as $item)
            <x-list-item :saved-keys="$savedKeys" :item="$item" :multi-member="$multiMember" :sortable="$sortable" wire:sort:item="{{ $item->id }}" />
        @endforeach
    </ul>
</section>
