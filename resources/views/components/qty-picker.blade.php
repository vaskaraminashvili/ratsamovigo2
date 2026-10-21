@props(['target'])

{{-- Quantity stepper + unit chips bound to an Alpine object in the surrounding scope (draft / edit). --}}
<div class="space-y-3">
    <div class="flex items-center gap-3">
        <div class="flex items-center rounded-full border border-ink-700 bg-ink-950">
            <button type="button" @click="qtyDec({{ $target }})" :disabled="{{ $target }}.qty === null"
                    class="grid size-10 place-items-center rounded-full text-ink-200 hover:bg-ink-800 disabled:opacity-30" aria-label="შემცირება">
                <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
            </button>
            <input type="text" inputmode="decimal" autocomplete="off" placeholder="–"
                   :value="{{ $target }}.qty === null ? '' : fmt({{ $target }}.qty)" @change="setQty({{ $target }}, $event.target.value)"
                   class="w-14 bg-transparent text-center text-[17px] font-semibold tabular-nums outline-none placeholder:text-ink-500" aria-label="რაოდენობა">
            <button type="button" @click="qtyInc({{ $target }})"
                    class="grid size-10 place-items-center rounded-full text-ink-200 hover:bg-ink-800" aria-label="გაზრდა">
                <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button>
        </div>
        <span class="min-w-0 flex-1 truncate text-[15px]" :class="{{ $target }}.qty === null ? 'text-ink-500' : 'font-medium text-ink-100'"
              x-text="{{ $target }}.qty === null ? 'რაოდენობის გარეშე' : fmt({{ $target }}.qty) + ' ' + unitFull({{ $target }}.unit)"></span>
        <button type="button" x-show="{{ $target }}.qty !== null" @click="{{ $target }}.qty = null" class="shrink-0 rounded-full px-2 py-1 text-[13px] text-ink-400 hover:text-ink-100">მოხსნა</button>
    </div>
    <div class="-mx-1 flex gap-1.5 overflow-x-auto px-1 pb-0.5 [scrollbar-width:none]" role="group" aria-label="ერთეული">
        <template x-for="u in units" :key="u.key">
            <button type="button" @click="setUnit({{ $target }}, u.key)" :aria-pressed="({{ $target }}.qty !== null && {{ $target }}.unit === u.key).toString()" :title="u.full"
                    class="shrink-0 rounded-full border px-3.5 py-2.5 text-[14px] transition"
                    :class="{{ $target }}.qty !== null && {{ $target }}.unit === u.key ? 'border-primary-line bg-primary-400 font-semibold text-on' : 'border-ink-700 text-ink-200 hover:border-ink-500'"
                    x-text="u.key"></button>
        </template>
    </div>
</div>
