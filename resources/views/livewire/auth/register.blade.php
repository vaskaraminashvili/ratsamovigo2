<main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1rem,env(safe-area-inset-top))]">
    <a href="{{ route('home') }}" wire:navigate class="-ml-2 grid size-11 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="უკან">
        <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    </a>

    <h1 class="mt-8 text-[2.1rem] font-extrabold leading-[1.15]">შექმენი ანგარიში</h1>
    <p class="mt-3 text-ink-300">შემდეგ შექმენი სია და მოიწვიე ისინი, ვისთვისაც ყიდულობ.</p>

    @if ($invitedTo)
        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-primary-line/30 bg-primary-400/10 p-3.5">
            <span class="grid size-9 place-items-center rounded-full font-bold text-on {{ $invitedTo->owner->avatarColorClass() }}">{{ $invitedTo->owner->initials() }}</span>
            <p class="text-[14px] leading-snug text-ink-200">ანგარიშის შექმნის შემდეგ შეუერთდები სიას <span class="font-semibold text-ink-100">„{{ $invitedTo->name }}“</span>.</p>
        </div>
    @endif

    <form wire:submit="register" class="mt-8 space-y-5" novalidate>
        <x-field label="შენი სახელი" name="name" hint="ჩანს ნივთებთან, რომლებსაც დაამატებ." autocomplete="given-name" enterkeyhint="next" />
        <x-field label="ელფოსტა" name="email" type="email" autocomplete="email" inputmode="email" enterkeyhint="next" />
        <x-password-field label="პაროლი" name="password" hint="მინიმუმ 8 სიმბოლო." autocomplete="new-password" enterkeyhint="go" />

        <p class="text-[13px] text-ink-400">ანგარიშის შექმნით ეთანხმები <a href="{{ route('terms') }}" class="font-semibold text-ink-200 underline" target="_blank" rel="noopener">პირობებს</a> და <a href="{{ route('privacy') }}" class="font-semibold text-ink-200 underline" target="_blank" rel="noopener">კონფიდენციალურობის პოლიტიკას</a>.</p>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="register">ანგარიშის შექმნა</button>
    </form>

    <p class="mt-auto pt-10 text-center text-ink-300">
        უკვე გაქვს ანგარიში?
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-primary-fg">შესვლა</a>
    </p>
</main>
