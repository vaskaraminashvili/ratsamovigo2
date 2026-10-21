<main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1rem,env(safe-area-inset-top))]">
    <a href="{{ route('login') }}" wire:navigate class="-ml-2 grid size-11 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="უკან">
        <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    </a>

    <h1 class="mt-8 text-[2.1rem] font-extrabold leading-[1.15]">დაგავიწყდა პაროლი?</h1>
    <p class="mt-3 text-ink-300">ჩაწერე ელფოსტა და გამოგიგზავნით ბმულს ახალი პაროლისთვის.</p>

    @if ($sent)
        <div class="mt-8 rounded-2xl border border-cart-400/50 bg-cart-400/10 p-4" role="status">
            <p class="font-semibold">შეამოწმე ელფოსტა</p>
            <p class="mt-1 text-[15px] text-ink-200">თუ ეს მისამართი რეგისტრირებულია, ბმული გაიგზავნა. ის 60 წუთის განმავლობაში მუშაობს.</p>
        </div>
    @else
        <form wire:submit="sendLink" class="mt-10 space-y-5" novalidate>
            <x-field label="ელფოსტა" name="email" type="email" autocomplete="email" inputmode="email" enterkeyhint="send" placeholder="you@example.com" />
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="sendLink">ბმულის გაგზავნა</button>
        </form>
    @endif

    <p class="mt-auto pt-10 text-center text-ink-300">
        გაგახსენდა?
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-primary-fg">შესვლა</a>
    </p>
</main>
