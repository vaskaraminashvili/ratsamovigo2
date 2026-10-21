<main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1rem,env(safe-area-inset-top))]">
    <h1 class="mt-16 text-[2.1rem] font-extrabold leading-[1.15]">ახალი პაროლი</h1>
    <p class="mt-3 text-ink-300">აირჩიე პაროლი, რომლითაც შემდეგ შეხვალ.</p>

    <form wire:submit="resetPassword" class="mt-10 space-y-5" novalidate>
        <x-field label="ელფოსტა" name="email" type="email" autocomplete="email" inputmode="email" enterkeyhint="next" />
        <x-password-field label="ახალი პაროლი" name="password" hint="მინიმუმ 8 სიმბოლო." autocomplete="new-password" enterkeyhint="go" />
        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="resetPassword">პაროლის შეცვლა</button>
    </form>

    <p class="mt-auto pt-10 text-center text-ink-300">
        <a href="{{ route('password.request') }}" wire:navigate class="font-semibold text-primary-fg">მოითხოვე ახალი ბმული</a>
    </p>
</main>
