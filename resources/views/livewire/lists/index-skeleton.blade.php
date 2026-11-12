<section role="status" aria-busy="true">
    <span class="sr-only">იტვირთება…</span>
    <main class="px-5" aria-hidden="true">
        <section class="pt-4">
            <div class="skeleton h-9 w-3/4 rounded-xl"></div>
        </section>

        <section class="mt-10">
            <div class="skeleton h-5 w-28 rounded-md"></div>
            <ul class="mt-2 divide-y divide-ink-800">
                @foreach (['w-3/5', 'w-2/5', 'w-4/6'] as $titleWidth)
                    <li class="flex items-center gap-4 py-4">
                        <div class="skeleton size-14 shrink-0 rounded-2xl"></div>
                        <div class="min-w-0 flex-1">
                            <div class="skeleton h-5 rounded-md {{ $titleWidth }}"></div>
                            <div class="skeleton mt-2 h-3.5 w-24 rounded-md"></div>
                            <div class="skeleton mt-3 h-1 rounded-full"></div>
                        </div>
                        <div class="flex -space-x-2">
                            <div class="skeleton size-8 rounded-full ring-2 ring-ink-950"></div>
                            <div class="skeleton size-8 rounded-full ring-2 ring-ink-950"></div>
                        </div>
                        <div class="size-11 shrink-0"></div>
                    </li>
                @endforeach
            </ul>
        </section>
    </main>
</section>
