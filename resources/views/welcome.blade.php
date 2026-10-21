<x-layouts::guest>
    <header class="mx-auto flex max-w-md items-center justify-between px-5 pt-[max(1.25rem,env(safe-area-inset-top))] sm:max-w-2xl">
        <x-app-logo :href="route('home')" />
        <a href="{{ route('login') }}" wire:navigate class="rounded-full px-4 py-2 text-[15px] font-medium text-ink-200 hover:bg-ink-800">შესვლა</a>
    </header>

    <main class="mx-auto max-w-md px-5 sm:max-w-2xl">
        <section class="pb-10 pt-12 sm:pt-20">
            <h1 class="text-[2.35rem] font-extrabold leading-[1.12] sm:text-6xl">აღარ დარეკო სახლში, რომ იკითხო, რა იყიდო.</h1>
            <p class="mt-5 max-w-[36ch] text-[17px] leading-relaxed text-ink-300">
                ერთი საერთო სია მთელი ოჯახისთვის. ვინც სახლშია, ამატებს, რაც აკლია. ვინც გარეთაა, ხედავს სიას და მაღაზიაში ნიშნავს ნაყიდს.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" wire:navigate class="btn-primary">შექმენი უფასო სია</a>
                <a href="{{ route('login') }}" wire:navigate class="btn-ghost">შესვლა</a>
            </div>
        </section>

        <section aria-label="როგორ გამოიყურება საღამო ამ სიით" class="relative pb-16">
            <div class="call relative rounded-3xl border border-ink-800 bg-ink-900 p-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-full bg-ink-700 text-ink-200">
                        <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="font-semibold leading-tight">სახლი</p>
                        <p class="text-[13px] text-ink-400">ზარი, 19:04</p>
                    </div>
                    <span class="grid size-9 place-items-center rounded-full bg-urgent-500/90 text-white">
                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </span>
                </div>
                <div class="mt-4 space-y-2 text-[15px]">
                    <p class="ml-auto w-fit max-w-[80%] rounded-2xl rounded-br-md bg-ink-700 px-3.5 py-2">მაღაზიასთან ვარ. რამე ხომ არ გინდათ?</p>
                    <p class="w-fit max-w-[80%] rounded-2xl rounded-bl-md bg-ink-800 px-3.5 py-2">რძე? მოიცა, მაცივარში ჩავიხედავ…</p>
                    <p class="ml-auto w-fit rounded-2xl rounded-br-md bg-ink-700 px-3.5 py-2">ალო?</p>
                </div>
                <span class="call-line pointer-events-none absolute left-6 right-6 top-1/2 h-[3px] rounded-full bg-urgent-400"></span>
            </div>

            <div class="relative z-10 -mt-10 ml-6 rounded-3xl border border-ink-700 bg-ink-850 p-4 shadow-[0_30px_60px_-20px_var(--shade)] sm:ml-24">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-lg font-bold leading-tight">სახლის საყიდლები</p>
                        <p class="mt-0.5 flex items-center gap-1 text-[13px] text-ink-400">
                            ნინო ამატებს ნივთებს
                            <span class="dot size-1 rounded-full bg-ink-300"></span><span class="dot size-1 rounded-full bg-ink-300 [animation-delay:.2s]"></span><span class="dot size-1 rounded-full bg-ink-300 [animation-delay:.4s]"></span>
                        </p>
                    </div>
                    <div class="flex shrink-0 -space-x-2">
                        <span class="grid size-8 place-items-center rounded-full bg-m1 text-sm font-bold text-on ring-2 ring-ink-850">ნ</span>
                        <span class="grid size-8 place-items-center rounded-full bg-m2 text-sm font-bold text-on ring-2 ring-ink-850">გ</span>
                        <span class="grid size-8 place-items-center rounded-full bg-m3 text-sm font-bold text-on ring-2 ring-ink-850">დ</span>
                    </div>
                </div>
                <ul class="mt-4 divide-y divide-ink-700/70">
                    <li class="pop flex items-center gap-3 py-2.5 [animation-delay:3.1s]">
                        <span class="h-5 w-1 rounded-full bg-urgent-400"></span>
                        <span class="flex-1 font-medium">მაწონი <span class="ml-1 rounded-md bg-ink-800 px-1.5 py-0.5 text-[12px] font-semibold">2 ცალი</span></span>
                        <span class="rounded-full bg-urgent-400/15 px-2 py-0.5 text-xs font-semibold text-urgent-300">სასწრაფო</span>
                    </li>
                    <li class="pop flex items-center gap-3 py-2.5 [animation-delay:3.6s]">
                        <span class="h-5 w-1 rounded-full bg-urgent-400"></span>
                        <span class="flex-1 font-medium">შოთის პური <span class="ml-1 rounded-md bg-ink-800 px-1.5 py-0.5 text-[12px] font-semibold">2 ცალი</span></span>
                        <span class="rounded-full bg-urgent-400/15 px-2 py-0.5 text-xs font-semibold text-urgent-300">სასწრაფო</span>
                    </li>
                    <li class="pop flex items-center gap-3 py-2.5 [animation-delay:4.1s]">
                        <span class="h-5 w-1 rounded-full bg-ink-600"></span>
                        <span class="flex-1 font-medium">პომიდორი <span class="ml-1 rounded-md bg-ink-800 px-1.5 py-0.5 text-[12px] font-semibold">1 კგ</span></span>
                        <span class="flex items-center gap-1 rounded-full bg-ink-800 py-0.5 pl-0.5 pr-2 text-xs text-ink-200"><span class="grid size-5 place-items-center rounded-full bg-m2 text-[10px] font-bold text-on">გ</span>გიორგი</span>
                    </li>
                    <li class="pop flex items-center gap-3 py-2.5 [animation-delay:4.6s]">
                        <span class="h-5 w-1 rounded-full bg-ink-600"></span>
                        <span class="flex-1 font-medium">ქინძი <span class="ml-1 rounded-md bg-ink-800 px-1.5 py-0.5 text-[12px] font-semibold">1 კონა</span></span>
                        <span class="rounded-full border border-ink-700 px-2 py-0.5 text-xs text-ink-300">ვიყიდი</span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="border-t border-ink-800 py-12">
            <h2 class="text-3xl font-bold">ერთ წუთში მზადაა</h2>
            <ol class="mt-8 space-y-7">
                @foreach ([
                    ['შექმენი სია', '„სახლის საყიდლები“, „ბებიასთან შაბათ-კვირას“ ან ნებისმიერი სხვა.'],
                    ['გაუგზავნე მოსაწვევი ბმული', 'ჩააგდე ოჯახის ჩატში. ვინც გახსნის, სიას შეუერთდება.'],
                    ['ისინი ამატებენ, შენ ყიდულობ', 'ნივთები მაშინვე ჩნდება. მონიშნე, როცა კალათაში ჩადებ.'],
                ] as [$title, $text])
                    <li class="flex gap-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full border border-ink-600 font-bold text-primary-fg">{{ $loop->iteration }}</span>
                        <div><p class="text-lg font-semibold">{{ $title }}</p><p class="mt-1 text-ink-300">{{ $text }}</p></div>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="border-t border-ink-800 py-12">
            <h2 class="text-3xl font-bold">შექმნილია სახლისკენ გზისთვის</h2>
            <dl class="mt-8 divide-y divide-ink-800">
                @foreach ([
                    ['text-urgent-fg', 'სასწრაფო ყოველთვის ზემოთაა', 'მონიშნე, რაც დღეს საღამოს გჭირდება, რომ არ დაგავიწყდეს.'],
                    ['text-ink-200', 'რაოდენობა და ერთეული', '2 კგ კარტოფილი, 1 ლ რძე, 1 კონა ქინძი. აღარ მოგიწევს კითხვა, რამდენი.'],
                    ['text-ink-200', '„ვიყიდი“', 'აიღე ნივთი შენზე, რომ ორმა ერთი და იგივე არ იყიდოს.'],
                    ['text-ink-200', 'გამოკითხვა', 'ღვინო, ლუდი თუ არაყი? ოჯახი ხმას აძლევს და გამარჯვებული სიაში ხვდება.'],
                    ['text-cart-fg', 'ყოველდღიური სია', 'ნაყიდი იმალება და შემდეგ ჯერზე ისევ ბრუნდება სიაში. კვერცხი დღესაც და ხვალაც.'],
                    ['text-primary-fg', 'შენახული ნივთები და კერძები', 'შეინახე, რასაც ხშირად ყიდულობ, ან ხაჭაპურის ყველა ინგრედიენტი. ნებისმიერ სიაში ერთი შეხებით დაამატებ.'],
                ] as [$color, $title, $text])
                    <div class="flex gap-4 py-5">
                        <span class="mt-0.5 size-6 shrink-0 rounded-full border-2 border-current {{ $color }}" aria-hidden="true"></span>
                        <div><dt class="font-semibold">{{ $title }}</dt><dd class="mt-1 text-ink-300">{{ $text }}</dd></div>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="pb-16 pt-4">
            <div class="rounded-[2rem] bg-primary-400 p-7 text-on">
                <p class="text-[1.8rem] font-extrabold leading-[1.15]">შემდეგ ჯერზე ზარი აღარ დაგჭირდება.</p>
                <p class="mt-3 text-on/75">უფასოა მთელი ოჯახისთვის.</p>
                <a href="{{ route('register') }}" wire:navigate class="btn mt-6 w-full bg-ink-950 text-ink-100 hover:bg-ink-900">შექმენი უფასო სია</a>
            </div>
        </section>
    </main>

    <footer class="mx-auto max-w-md border-t border-ink-800 px-5 py-8 text-sm text-ink-500 sm:max-w-2xl">
        <p>© {{ now()->year }} რა წამოვიღო</p>
        <p class="mt-2 flex gap-4">
            <a href="{{ route('privacy') }}" wire:navigate class="hover:text-ink-300">კონფიდენციალურობა</a>
            <a href="{{ route('terms') }}" wire:navigate class="hover:text-ink-300">პირობები</a>
        </p>
    </footer>
</x-layouts::guest>
