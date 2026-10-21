<main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1rem,env(safe-area-inset-top))]">
    <a href="{{ route('home') }}" wire:navigate class="-ml-2 grid size-11 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="უკან">
        <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    </a>

    <h1 class="mt-8 text-[2.1rem] font-extrabold leading-[1.15]">კეთილი იყოს შენი დაბრუნება</h1>
    <p class="mt-3 text-ink-300">შედი და ნახე, რა სჭირდება ოჯახს.</p>

    @if (session('status'))
        <p class="mt-6 rounded-2xl border border-cart-400/50 bg-cart-400/10 p-3.5 text-[15px]" role="status">{{ session('status') }}</p>
    @endif

    <form wire:submit="login" class="mt-10 space-y-5" novalidate>
        <x-field label="ელფოსტა" name="email" type="email" autocomplete="email" inputmode="email" enterkeyhint="next" placeholder="you@example.com" />

        <x-password-field label="პაროლი" name="password" autocomplete="current-password" enterkeyhint="go">
            <x-slot:aside>
                <a href="{{ route('password.request') }}" wire:navigate class="text-[14px] font-medium text-primary-fg">დაგავიწყდა პაროლი?</a>
            </x-slot:aside>
        </x-password-field>

        <label class="flex items-center gap-3 text-[15px] text-ink-200">
            <input type="checkbox" wire:model="remember" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400">
            დამიმახსოვრე
        </label>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="login">შესვლა</button>
    </form>

    <p class="mt-auto pt-10 text-center text-ink-300">
        ახალი ხარ?
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-primary-fg">შექმენი ანგარიში</a>
    </p>
</main>
