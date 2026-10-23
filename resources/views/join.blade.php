<x-layouts::guest title="მოსაწვევი">
    <main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1.25rem,env(safe-area-inset-top))]">
        <x-app-logo :href="route('home')" />

        <div class="my-auto py-10">
            <div class="flex -space-x-3">
                @foreach ($members->take(3) as $member)
                    <x-avatar :user="$member" class="size-14 rounded-full text-xl font-bold text-on ring-4 ring-ink-950" />
                @endforeach
                <span class="grid size-14 place-items-center rounded-full border-2 border-dashed border-ink-600 text-ink-400 ring-4 ring-ink-950">
                    <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </span>
            </div>

            <h1 class="mt-8 text-[2.1rem] font-extrabold leading-[1.15]">{{ $list->owner->name }}-მ მოგიწვია სიაში „{{ $list->name }}“</h1>
            <p class="mt-4 text-ink-300">შეუერთდი, ნახე რა გვჭირდება, დაამატე შენი ნივთები და მონიშნე ნაყიდი.</p>

            <div class="mt-8 flex gap-6 border-y border-ink-800 py-4 text-[15px]">
                <p><span class="block text-2xl font-bold">{{ $remaining }}</span><span class="text-ink-400">ნივთი დარჩა</span></p>
                <p><span class="block text-2xl font-bold text-urgent-fg">{{ $urgent }}</span><span class="text-ink-400">სასწრაფო</span></p>
                <p><span class="block text-2xl font-bold">{{ $members->count() }}</span><span class="text-ink-400">წევრი</span></p>
            </div>
        </div>

        <div class="space-y-3">
            @if ($isMember)
                <p class="rounded-2xl border border-cart-400/50 bg-cart-400/10 p-3.5 text-center font-medium" role="status">უკვე წევრი ხარ ამ სიაში</p>
                <a href="{{ route('lists.show', $list) }}" class="btn-primary w-full">სიის გახსნა</a>
            @elseif (auth()->check())
                <form method="POST" action="{{ route('join.store', $list->invite_token) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full">სიას შეერთება</button>
                </form>
            @else
                <a href="{{ route('register') }}" class="btn-primary w-full">ანგარიშის შექმნა და შეერთება</a>
                <a href="{{ route('login') }}" class="btn-ghost w-full">უკვე მაქვს ანგარიში</a>
            @endif
        </div>
    </main>
</x-layouts::guest>
