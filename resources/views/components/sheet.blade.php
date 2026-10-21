@props(['name', 'label'])

{{-- Bottom sheet. Opens when the surrounding Alpine scope has `sheet === name`. --}}
<div x-show="sheet === '{{ $name }}'" x-cloak class="fixed inset-0 z-40" @keydown.escape.window="sheet = null">
    <div x-show="sheet === '{{ $name }}'" x-transition.opacity.duration.300ms class="absolute inset-0 bg-black/60" @click="sheet = null"></div>
    <section x-show="sheet === '{{ $name }}'" x-trap.noscroll="sheet === '{{ $name }}'" role="dialog" aria-modal="true" aria-label="{{ $label }}"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 mx-auto max-h-[90dvh] max-w-md overflow-y-auto rounded-t-[28px] border-t border-ink-700 bg-ink-900 px-5 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
        <div class="mx-auto mt-3 h-1.5 w-10 rounded-full bg-ink-700"></div>
        {{ $slot }}
    </section>
</div>
