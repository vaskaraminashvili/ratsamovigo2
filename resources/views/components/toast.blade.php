{{-- Listens for the Livewire "toast" event: $this->dispatch('toast', message: '...') --}}
<div x-data="{ message: '', timer: null }"
     x-on:toast.window="message = $event.detail.message; clearTimeout(timer); timer = setTimeout(() => message = '', 2200)"
     x-show="message" x-cloak x-transition role="status" aria-live="polite" aria-atomic="true"
     class="fixed inset-x-0 top-[max(1rem,env(safe-area-inset-top))] z-50 mx-auto w-fit max-w-[90%] rounded-full bg-ink-100 px-5 py-2.5 text-[15px] font-medium text-ink-950 shadow-xl"
     x-text="message"></div>
