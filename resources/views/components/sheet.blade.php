@props(['name', 'label'])

{{-- Bottom sheet. Opens when the surrounding Alpine scope has `sheet === name`. --}}
<div x-show="sheet === '{{ $name }}'" x-cloak class="fixed inset-0 z-40" @keydown.escape.window="sheet = null">
    <div x-show="sheet === '{{ $name }}'" x-transition.opacity.duration.300ms class="absolute inset-0 bg-black/60" @click="sheet = null"></div>
    {{-- Swipe down from the top of the sheet (handle, or any touch while scrolled to the top) to close it. --}}
    <section x-data="{ startY: null, dy: 0 }" x-show="sheet === '{{ $name }}'" x-trap.noscroll="sheet === '{{ $name }}'" role="dialog" aria-modal="true" aria-label="{{ $label }}"
             @touchstart="startY = $el.scrollTop <= 0 ? $event.touches[0].clientY : null; dy = 0"
             @touchmove="if (startY === null) return; dy = Math.max(0, $event.touches[0].clientY - startY); if (dy > 0) { $event.preventDefault(); $el.style.transform = `translateY(${dy}px)`; $el.style.transition = 'none'; }"
             @touchend="if (startY === null) return; $el.style.transition = ''; $el.style.transform = ''; if (dy > 90) { sheet = null; } startY = null; dy = 0"
             @touchcancel="$el.style.transition = ''; $el.style.transform = ''; startY = null; dy = 0"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 mx-auto max-h-[90dvh] max-w-md overflow-y-auto rounded-t-[28px] border-t border-ink-700 bg-ink-900 px-5 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
        <div class="mx-auto flex h-8 w-24 items-center justify-center" aria-hidden="true"><div class="h-1.5 w-10 rounded-full bg-ink-700"></div></div>
        {{ $slot }}
    </section>
</div>
