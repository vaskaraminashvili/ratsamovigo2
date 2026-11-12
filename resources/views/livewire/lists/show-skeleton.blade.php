<section class="mx-auto max-w-md pb-44" role="status" aria-busy="true">
    <span class="sr-only">იტვირთება…</span>

    <header class="px-3 pb-3 pt-[calc(max(.75rem,env(safe-area-inset-top))+10px)]" aria-hidden="true">
        <div class="flex items-center gap-3">
            <div class="skeleton size-8 shrink-0 rounded-full"></div>
            <div class="skeleton h-6 w-1/2 rounded-lg"></div>
            <div class="skeleton ml-auto size-7 rounded-full"></div>
        </div>
        <div class="skeleton ml-11 mt-2 h-3.5 w-20 rounded-md"></div>
    </header>

    <main class="px-4" aria-hidden="true">
        <div class="skeleton h-12 rounded-2xl"></div>

        <div class="skeleton mt-7 h-5 w-24 rounded-md"></div>
        <ul class="mt-2.5 space-y-1.5">
            @foreach (['w-1/2', 'w-2/3', 'w-2/5', 'w-3/5', 'w-1/2', 'w-1/3'] as $nameWidth)
                <li class="flex h-14 items-center gap-3 rounded-2xl border border-ink-800 px-3.5">
                    <div class="skeleton size-6 shrink-0 rounded-full"></div>
                    <div class="skeleton h-4 rounded-md {{ $nameWidth }}"></div>
                    <div class="skeleton ml-auto size-6 shrink-0 rounded-full"></div>
                </li>
            @endforeach
        </ul>
    </main>

    <div class="fixed inset-x-0 bottom-0 mx-auto max-w-md px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3" aria-hidden="true">
        <div class="skeleton h-12 rounded-full"></div>
    </div>
</section>
